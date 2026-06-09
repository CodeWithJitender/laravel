<?php

namespace App\Services\Export;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CsvExportService
{
    /**
     * Generate CSV file and save to storage.
     * Returns the relative storage path.
     */
    public function generate(string $reportName, array $columns, $data): string
    {
        $fileName = 'reports/' . Str::slug($reportName) . '_' . time() . '.csv';
        
        $tempFile = fopen('php://temp', 'r+');

        // Write columns headers
        fputcsv($tempFile, array_values($columns));

        // Write rows data
        foreach ($data as $row) {
            $rowData = [];
            foreach (array_keys($columns) as $key) {
                // Handle nested relationships or attributes
                $rowData[] = $this->resolveValue($row, $key);
            }
            fputcsv($tempFile, $rowData);
        }

        rewind($tempFile);
        $content = stream_get_contents($tempFile);
        fclose($tempFile);

        Storage::disk('public')->put($fileName, $content);

        return $fileName;
    }

    /**
     * Resolve dynamic nested values (e.g. employee.employee_detail.joining_date)
     */
    protected function resolveValue($row, string $key)
    {
        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key);
            $current = $row;
            foreach ($parts as $part) {
                if (is_object($current)) {
                    $current = $current->{$part};
                } elseif (is_array($current)) {
                    $current = $current[$part] ?? null;
                } else {
                    return '';
                }
            }
            return $current ?? '';
        }

        return $row->{$key} ?? '';
    }
}
