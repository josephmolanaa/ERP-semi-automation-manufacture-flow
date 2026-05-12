<?php

namespace App\Services\Parsers;

use App\Services\PdfParserService;
use Illuminate\Support\Facades\Log;

class QuotationPdfParser
{
    protected PdfParserService $pdfParser;

    public function __construct(PdfParserService $pdfParser)
    {
        $this->pdfParser = $pdfParser;
    }

    /**
     * Parse quotation PDF and extract structured data
     * 
     * @param string|object $file File path or uploaded file
     * @return array Extracted data
     */
    public function parse($file): array
    {
        // Parse PDF to text
        $text = is_string($file) 
            ? $this->pdfParser->parseFile($file)
            : $this->pdfParser->parseUploadedFile($file);

        if (!$text) {
            return $this->emptyResult('Failed to parse PDF');
        }

        Log::info('Parsing Quotation PDF', ['text_length' => strlen($text)]);

        // Extract data
        $data = [
            'success' => true,
            'nomor' => $this->extractNomor($text),
            'tanggal' => $this->extractTanggal($text),
            'berlaku_sampai' => $this->extractBerlakuSampai($text),
            'customer' => $this->extractCustomer($text),
            'items' => $this->extractItems($text),
            'total_harga' => $this->extractTotal($text),
            'catatan' => $this->extractCatatan($text),
            'raw_text' => $text, // For debugging
        ];

        return $data;
    }

    /**
     * Extract quotation number
     */
    protected function extractNomor(string $text): ?string
    {
        // Try various patterns
        $patterns = [
            'quotation no',
            'quote no',
            'no. quotation',
            'nomor penawaran',
            'no penawaran',
            'quotation number',
        ];

        foreach ($patterns as $pattern) {
            $value = $this->pdfParser->extractAfterLabel($text, $pattern);
            if ($value) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Extract quotation date
     */
    protected function extractTanggal(string $text): ?string
    {
        $patterns = ['date', 'tanggal', 'quotation date', 'tgl'];

        foreach ($patterns as $pattern) {
            $date = $this->pdfParser->extractDate($text, $pattern);
            if ($date) {
                return $date;
            }
        }

        return null;
    }

    /**
     * Extract validity date
     */
    protected function extractBerlakuSampai(string $text): ?string
    {
        $patterns = [
            'valid until',
            'berlaku sampai',
            'valid till',
            'validity',
            'expire date',
        ];

        foreach ($patterns as $pattern) {
            $date = $this->pdfParser->extractDate($text, $pattern);
            if ($date) {
                return $date;
            }
        }

        // Default: 14 days from now
        return now()->addDays(14)->format('Y-m-d');
    }

    /**
     * Extract customer information
     */
    protected function extractCustomer(string $text): array
    {
        $customer = [
            'name' => null,
            'company' => null,
            'email' => null,
            'phone' => null,
        ];

        // Extract customer name
        $namePatterns = ['customer', 'client', 'kepada', 'to', 'attn'];
        foreach ($namePatterns as $pattern) {
            $name = $this->pdfParser->extractAfterLabel($text, $pattern);
            if ($name) {
                $customer['name'] = $name;
                break;
            }
        }

        // Extract company
        $companyPatterns = ['company', 'perusahaan', 'pt', 'cv'];
        foreach ($companyPatterns as $pattern) {
            $company = $this->pdfParser->extractAfterLabel($text, $pattern);
            if ($company) {
                $customer['company'] = $company;
                break;
            }
        }

        // Extract email
        if (preg_match('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $text, $matches)) {
            $customer['email'] = $matches[1];
        }

        // Extract phone
        if (preg_match('/(\+?[\d\s\-\(\)]{10,})/', $text, $matches)) {
            $customer['phone'] = trim($matches[1]);
        }

        return $customer;
    }

    /**
     * Extract line items from quotation
     */
    protected function extractItems(string $text): array
    {
        $items = [];

        // Try to find table structure
        // Common patterns: No | Item | Qty | Unit | Price | Subtotal
        
        // Split text into lines
        $lines = explode("\n", $text);
        
        $inTable = false;
        $tableStartIndex = null;

        // Find table start (look for header keywords)
        foreach ($lines as $index => $line) {
            $line = strtolower($line);
            if (
                (str_contains($line, 'item') || str_contains($line, 'part')) &&
                (str_contains($line, 'qty') || str_contains($line, 'quantity')) &&
                (str_contains($line, 'price') || str_contains($line, 'harga'))
            ) {
                $inTable = true;
                $tableStartIndex = $index + 1;
                break;
            }
        }

        if ($tableStartIndex === null) {
            return [];
        }

        // Parse table rows
        for ($i = $tableStartIndex; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            
            // Stop if we hit total/subtotal line
            if (preg_match('/^(total|subtotal|grand total)/i', $line)) {
                break;
            }

            // Skip empty lines
            if (empty($line)) {
                continue;
            }

            // Try to parse line as item
            $item = $this->parseItemLine($line);
            if ($item) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Parse single item line
     */
    protected function parseItemLine(string $line): ?array
    {
        // Remove multiple spaces
        $line = preg_replace('/\s+/', ' ', $line);
        
        // Try to extract: part_name, qty, unit, price, subtotal
        // This is a simple implementation - adjust based on your PDF format
        
        $parts = explode(' ', $line);
        
        if (count($parts) < 3) {
            return null;
        }

        // Extract numbers (likely qty, price, subtotal)
        $numbers = [];
        $textParts = [];
        
        foreach ($parts as $part) {
            $cleaned = str_replace([',', '.'], '', $part);
            if (is_numeric($cleaned)) {
                $numbers[] = (float) str_replace(',', '', $part);
            } else {
                $textParts[] = $part;
            }
        }

        if (count($numbers) < 2) {
            return null;
        }

        return [
            'part_name' => implode(' ', array_slice($textParts, 0, -1)) ?: 'Unknown Part',
            'material' => null,
            'qty' => $numbers[0] ?? 1,
            'satuan' => end($textParts) ?: 'pcs',
            'harga_satuan' => $numbers[1] ?? 0,
            'subtotal' => $numbers[2] ?? ($numbers[0] * $numbers[1]),
            'keterangan' => null,
        ];
    }

    /**
     * Extract total amount
     */
    protected function extractTotal(string $text): ?float
    {
        $patterns = [
            'grand total',
            'total',
            'total amount',
            'total harga',
            'jumlah',
        ];

        foreach ($patterns as $pattern) {
            $total = $this->pdfParser->extractNumber($text, $pattern);
            if ($total !== null) {
                return $total;
            }
        }

        return null;
    }

    /**
     * Extract notes/remarks
     */
    protected function extractCatatan(string $text): ?string
    {
        $patterns = ['notes', 'remarks', 'catatan', 'keterangan'];

        foreach ($patterns as $pattern) {
            $notes = $this->pdfParser->extractAfterLabel($text, $pattern);
            if ($notes) {
                return $notes;
            }
        }

        return null;
    }

    /**
     * Return empty result with error message
     */
    protected function emptyResult(string $error): array
    {
        return [
            'success' => false,
            'error' => $error,
            'nomor' => null,
            'tanggal' => null,
            'berlaku_sampai' => null,
            'customer' => [],
            'items' => [],
            'total_harga' => null,
            'catatan' => null,
        ];
    }
}
