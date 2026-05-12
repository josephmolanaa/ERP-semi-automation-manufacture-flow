<?php

namespace App\Services\Parsers;

use App\Services\PdfParserService;
use Illuminate\Support\Facades\Log;

class PoPdfParser
{
    protected PdfParserService $pdfParser;

    public function __construct(PdfParserService $pdfParser)
    {
        $this->pdfParser = $pdfParser;
    }

    /**
     * Parse PO PDF and extract structured data
     */
    public function parse($file): array
    {
        $text = is_string($file) 
            ? $this->pdfParser->parseFile($file)
            : $this->pdfParser->parseUploadedFile($file);

        if (!$text) {
            return $this->emptyResult('Failed to parse PDF');
        }

        Log::info('Parsing PO PDF', ['text_length' => strlen($text)]);

        return [
            'success' => true,
            'nomor_po' => $this->extractNomorPo($text),
            'tanggal_po' => $this->extractTanggalPo($text),
            'estimasi_selesai' => $this->extractEstimasiSelesai($text),
            'customer' => $this->extractCustomer($text),
            'total' => $this->extractTotal($text),
            'catatan' => $this->extractCatatan($text),
            'raw_text' => $text,
        ];
    }

    protected function extractNomorPo(string $text): ?string
    {
        $patterns = [
            'po no',
            'po number',
            'purchase order no',
            'nomor po',
            'no. po',
        ];

        foreach ($patterns as $pattern) {
            $value = $this->pdfParser->extractAfterLabel($text, $pattern);
            if ($value) {
                return $value;
            }
        }

        return null;
    }

    protected function extractTanggalPo(string $text): ?string
    {
        $patterns = ['po date', 'date', 'tanggal', 'order date'];

        foreach ($patterns as $pattern) {
            $date = $this->pdfParser->extractDate($text, $pattern);
            if ($date) {
                return $date;
            }
        }

        return now()->format('Y-m-d');
    }

    protected function extractEstimasiSelesai(string $text): ?string
    {
        $patterns = [
            'delivery date',
            'due date',
            'completion date',
            'estimasi selesai',
            'target selesai',
        ];

        foreach ($patterns as $pattern) {
            $date = $this->pdfParser->extractDate($text, $pattern);
            if ($date) {
                return $date;
            }
        }

        return now()->addDays(14)->format('Y-m-d');
    }

    protected function extractCustomer(string $text): array
    {
        $customer = [
            'name' => null,
            'company' => null,
        ];

        $namePatterns = ['vendor', 'supplier', 'from', 'customer', 'kepada'];
        foreach ($namePatterns as $pattern) {
            $name = $this->pdfParser->extractAfterLabel($text, $pattern);
            if ($name) {
                $customer['name'] = $name;
                break;
            }
        }

        $companyPatterns = ['company', 'perusahaan'];
        foreach ($companyPatterns as $pattern) {
            $company = $this->pdfParser->extractAfterLabel($text, $pattern);
            if ($company) {
                $customer['company'] = $company;
                break;
            }
        }

        return $customer;
    }

    protected function extractTotal(string $text): ?float
    {
        $patterns = ['total', 'grand total', 'total amount', 'jumlah'];

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
        $patterns = ['notes', 'remarks', 'catatan', 'keterangan', 'description'];

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
            'nomor_po' => null,
            'tanggal_po' => null,
            'estimasi_selesai' => null,
            'customer' => [],
            'total' => null,
            'catatan' => null,
        ];
    }
}
