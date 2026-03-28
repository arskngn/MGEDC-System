<?php

namespace App\Support;

final class CsvImportReader
{
    /**
     * Pick delimiter from the header line (Excel “CSV UTF-8” often uses semicolons).
     */
    public static function guessDelimiter(string $line): string
    {
        $line = str_replace("\r", '', $line);
        $comma = substr_count($line, ',');
        $semi = substr_count($line, ';');
        $tab = substr_count($line, "\t");

        if ($tab > $comma && $tab > $semi) {
            return "\t";
        }

        return $semi > $comma ? ';' : ',';
    }

    /**
     * Open a CSV path, skip UTF-8 BOM, read the first row as header with the correct delimiter.
     *
     * @return array{handle: resource, delimiter: string, header: list<string>}|null
     */
    public static function open(string $path): ?array
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return null;
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $line = fgets($handle);
        if ($line === false || trim($line) === '') {
            fclose($handle);

            return null;
        }

        $delimiter = self::guessDelimiter($line);
        $parsed = str_getcsv(rtrim($line, "\r\n"), $delimiter);
        if ($parsed === false || $parsed === []) {
            fclose($handle);

            return null;
        }

        $header = array_values($parsed);
        foreach ($header as $i => $col) {
            $header[$i] = trim((string) $col);
        }
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            $header[0] = trim($header[0]);
        }

        return [
            'handle' => $handle,
            'delimiter' => $delimiter,
            'header' => $header,
        ];
    }
}
