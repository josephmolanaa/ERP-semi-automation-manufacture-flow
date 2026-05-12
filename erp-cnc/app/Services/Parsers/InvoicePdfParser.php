<?php

namespace App\Services\Parsers;

use App\Services\PdfParserService;
use Illuminate\Support\Facades\Log;

class InvoicePdfParser
{
    protected PdfParserService $pdfParser;

    public function __construct(PdfParserService $pdfParser)
    {
        $this->pdfParser = $pdfParser;
    }

    /**
     * Parse Invoice PDF and extract structured data
     */
    public function parse($file): array
    {
        $text = is_string($file) 
            ? $this->pdfParser->parseFile($file)
            : $this->pdfParser->parseUploadedFile($file);

        if (!$text) {
            return $this->emptyResult('Failed to parse PDF');
        }

        Log::info('Parsing Invoice PDF', ['text_length' => strlen($text)]);

        return [
            'success' => true,
            'nomor_invoice' => $this->extractNomorInvoice($text),
            'tanggal' => $this->extractTanggal($text),
            'jatuh_tempo' => $this->extractJatuhTempo($text),
            'total' => $this->extractTotal($text),
            'catatan' => $this->extractCatatan($text),
            'raw_text' => $text,
        ];
    }

    protected function extractNomorInvoice(string $text): ?string
    {
        $patterns = [
            'invoice no',
            'invoice number',
            'nomor invoice',
            'no. invoice',
            'inv no',
        ];

        foreach ($patterns as $pattern) {
            $value = $this->pdfParser->extractAfterLabel($text, $pattern);
            if ($value) {
                return $value;
            }
        }

        return null;
    }

    protected function extractTanggal(string $text): ?string
    {
        $patterns = ['invoice date', 'date', 'tanggal'];

        foreach ($patterns as $pattern) {
            $date = $this->pdfParser->extractDate($text, $pattern);
            if ($date) {
                return $date;
            }
        }

        return now()->format('Y-m-d');
    }

    protected function extractJatuhTempo(string $text): ?string
    {
        $patterns = [
            'due date',
            'payment due',
            'jatuh tempo',
            'tanggal jatuh tempo',
        ];

        foreach ($patterns as $pattern) {
            $date = $this->pdfParser->extractDate($text, $pattern);
            if ($date) {
                return $date;
            }
        }

        return now()->addDays(30)->format('Y-m-d');
    }

    protected function extractTotal(string $text): ?float
    {
        $patterns = [
            'total due',
            'amount due',
            'total',
            'grand total',
            'total amount',
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

    protected function emptyResult(string $error): array
    {
        return [
            'success' => false,
            'error' => $error,
            'nomor_invoice' => null,
            'tanggal' => null,
            'jatuh_tempo' => null,
            'total' => null,
            'catatan' => null,
        ];
    }
}
