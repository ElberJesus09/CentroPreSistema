<?php

namespace App\Services\Academic;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use ZipArchive;

class AcademicFileParser
{
    private const int MAX_XML_ENTRY_BYTES = 2_000_000;

    /**
     * @return array<int, array<int, string>>
     */
    public function rows(UploadedFile $file): array
    {
        $extension = mb_strtolower($file->getClientOriginalExtension());

        return $this->rowsFromPath($file->getRealPath() ?: '', $extension);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function rowsFromPath(string $path, string $extension): array
    {
        $extension = mb_strtolower($extension);

        return match ($extension) {
            'xlsx' => $this->xlsxRows($path),
            'csv', 'txt' => $this->delimitedRows($path),
            default => throw new RuntimeException('El formato del archivo no es compatible.'),
        };
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function delimitedRows(string $path): array
    {
        $content = (string) file_get_contents($path);
        $lines = preg_split('/\R/u', $content) ?: [];
        $separator = str_contains($content, '|') && substr_count($content, '|') >= substr_count($content, ',') ? '|' : ',';
        $rows = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $rows[] = array_map(fn (string $value): string => trim($value), str_getcsv($line, $separator));
        }

        return $this->removeHeader($rows);
    }

    /**
     * Lector XLSX liviano para la primera hoja. Evita dependencias externas.
     *
     * @return array<int, array<int, string>>
     */
    private function xlsxRows(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('No se pudo leer el archivo Excel.');
        }

        $this->assertEntryIsSmallEnough($zip, 'xl/worksheets/sheet1.xml');
        $this->assertEntryIsSmallEnough($zip, 'xl/sharedStrings.xml', required: false);

        $sharedStrings = $this->sharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('El archivo Excel no contiene una hoja válida.');
        }

        $xml = simplexml_load_string($sheetXml, options: LIBXML_NONET);
        if ($xml === false) {
            throw new RuntimeException('El archivo Excel tiene una estructura inválida.');
        }

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $cell) {
                $index = $this->columnIndex((string) $cell['r']);
                $type = (string) $cell['t'];
                $value = (string) ($cell->v ?? '');
                $cells[$index] = $type === 's' ? ($sharedStrings[(int) $value] ?? '') : trim($value);
            }
            if ($cells !== []) {
                ksort($cells);
                $rows[] = array_values($cells);
            }
        }

        return $this->removeHeader($rows);
    }

    /**
     * @return array<int, string>
     */
    private function sharedStrings(ZipArchive $zip): array
    {
        $xmlContent = $zip->getFromName('xl/sharedStrings.xml');
        if ($xmlContent === false) {
            return [];
        }

        $xml = simplexml_load_string($xmlContent, options: LIBXML_NONET);
        if ($xml === false) {
            return [];
        }

        $strings = [];
        foreach ($xml->si as $item) {
            $strings[] = trim((string) ($item->t ?? $item->r->t ?? ''));
        }

        return $strings;
    }

    private function columnIndex(string $cellReference): int
    {
        preg_match('/^[A-Z]+/i', $cellReference, $matches);
        $letters = strtoupper($matches[0] ?? 'A');
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = $index * 26 + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }

    private function assertEntryIsSmallEnough(ZipArchive $zip, string $entryName, bool $required = true): void
    {
        $stat = $zip->statName($entryName);

        if ($stat === false) {
            if ($required) {
                throw new RuntimeException('El archivo Excel no contiene una hoja vÃ¡lida.');
            }

            return;
        }

        if (($stat['size'] ?? 0) > self::MAX_XML_ENTRY_BYTES) {
            throw new RuntimeException('El archivo Excel es demasiado grande para procesarlo de forma segura.');
        }
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @return array<int, array<int, string>>
     */
    private function removeHeader(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $first = array_map(fn (string $value): string => mb_strtolower(trim($value)), $rows[0]);
        if (in_array('dni', $first, true) || in_array('nota', $first, true) || in_array('evaluacion', $first, true)) {
            array_shift($rows);
        }

        return array_values($rows);
    }
}
