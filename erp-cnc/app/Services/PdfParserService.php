<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Log;

class PdfParserService
{
    protected Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Parse PDF file and extract text content
     */
    public function parseFile(string $filePath): ?string
    {
        try {
            $pdf = $this->parser->parseFile($filePath);
            $text = $pdf->getText();
            
            return $this->cleanText($text);
        } catch (\Exception $e) {
            Log::error('PDF Parsing Error: ' . $e->getMessage(), [
                'file' => $filePath,
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    /**
     * Parse PDF from uploaded file
     */
    public function parseUploadedFile($file): ?string
    {
        if (!$file) {
            return null;
        }

        $path = $file->getRealPath();
        return $this->parseFile($path);
    }

    /**
     * Clean extracted text (remove extra spaces, newlines, etc.)
     */
    protected function cleanText(string $text): string
    {
        // Remove multiple spaces
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Remove multiple newlines
        $text = preg_replace('/\n+/', "\n", $text);
        
        // Trim
        return trim($text);
    }

    /**
     * Extract value after a label/keyword
     * Example: "Invoice No: INV-001" -> "INV-001"
     */
    public function extractAfterLabel(string $text, string $label): ?string
    {
        $pattern = '/' . preg_quote($label, '/') . '\s*:?\s*([^\n]+)/i';
        
        if (preg_match($pattern, $text, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }

    /**
     * Extract date in various formats
     */
    public function extractDate(string $text, string $label = 'date'): ?string
    {
        $value = $this->extractAfterLabel($text, $label);
        
        if (!$value) {
            return null;
        }

        // Try to parse various date formats
        $formats = [
            'd/m/Y', 'd-m-Y', 'Y-m-d', 'd M Y', 'd F Y',
            'm/d/Y', 'm-d-Y'
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * Extract currency/number value
     */
    public function extractNumber(string $text, string $label): ?float
    {
        $value = $this->extractAfterLabel($text, $label);
        
        if (!$value) {
            return null;
        }

        // Remove currency symbols and formatting
        $cleaned = preg_replace('/[^\d.,]/', '', $value);
        $cleaned = str_replace(',', '', $cleaned);
        
        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    /**
     * Extract table data (simple implementation)
     * Returns array of rows
     */
    public function extractTable(string $text, int $startLine, int $endLine): array
    {
        $lines = explode("\n", $text);
        $tableLines = array_slice($lines, $startLine, $endLine - $startLine);
        
        $rows = [];
        foreach ($tableLines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            
            // Split by multiple spaces (assuming columns are space-separated)
            $columns = preg_split('/\s{2,}/', $line);
            $rows[] = array_map('trim', $columns);
        }
        
        return $rows;
    }
}
