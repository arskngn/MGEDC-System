<?php

namespace App\Services;

use App\Support\CsvImportReader;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportExportService
{
    /**
     * Generate a CSV response for downloading.
     */
    public function downloadCsv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            // Excel-compatible UTF-8 BOM
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, $headers);

            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Open a CSV file for reading and return the handle, delimiter, and header map.
     */
    public function openCsv(string $path): ?array
    {
        $opened = CsvImportReader::open($path);
        if ($opened === null) {
            return null;
        }

        $header = $opened['header'];
        $map = [];
        foreach ($header as $i => $col) {
            $key = strtolower(trim((string) $col));
            $map[$key] = $i;
        }

        return [
            'handle' => $opened['handle'],
            'delimiter' => $opened['delimiter'],
            'map' => $map,
        ];
    }

    /**
     * Check if a CSV row is empty.
     */
    public function isRowEmpty(array $row): bool
    {
        return empty(array_filter($row, fn($val) => trim((string)$val) !== ''));
    }
}
