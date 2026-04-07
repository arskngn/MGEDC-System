<?php

namespace App\Traits;

/**
 * Trait CsvSanitization
 * 
 * Provides methods to sanitize values for CSV export to prevent formula injection attacks.
 * Formula injection occurs when CSV values starting with =, +, -, or @ are interpreted
 * as formulas by spreadsheet applications like Excel.
 */
trait CsvSanitization
{
    /**
     * Sanitize a single value for CSV export
     *
     * Prepends a single quote (') to values starting with formula-injection-prone characters
     * This prevents spreadsheet applications from interpreting them as formulas.
     *
     * @param mixed $value The value to sanitize
     * @return string The sanitized value
     */
    protected function sanitizeForCsv(mixed $value): string
    {
        $value = (string) $value;
        
        // Check if value starts with formula-injection characters
        if (preg_match('/^[=+\-@]/', $value)) {
            return "'" . $value;
        }
        
        return $value;
    }

    /**
     * Sanitize an array of values for CSV export
     *
     * @param array $row Array of values
     * @return array The sanitized row
     */
    protected function sanitizeCsvRow(array $row): array
    {
        return array_map(
            fn($value) => $this->sanitizeForCsv($value),
            $row
        );
    }

    /**
     * Sanitize multiple rows for CSV export
     *
     * @param array $rows Array of rows
     * @return array The sanitized rows
     */
    protected function sanitizeCsvRows(array $rows): array
    {
        return array_map(
            fn($row) => $this->sanitizeCsvRow($row),
            $rows
        );
    }
}
