<?php

namespace App\Services\Export;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExcelExportService
{
    /**
     * Generate styled Excel (HTML spreadsheet format) file and save to storage.
     * Returns the relative storage path.
     */
    public function generate(string $reportName, array $columns, $data): string
    {
        $fileName = 'reports/' . Str::slug($reportName) . '_' . time() . '.xls';
        
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta http-equiv="Content-type" content="text/html;charset=utf-8" />';
        $html .= '<style>';
        $html .= 'table { border-collapse: collapse; font-family: Calibri, sans-serif; }';
        $html .= 'th { background-color: #4F46E5; color: #ffffff; font-weight: bold; border: 1px solid #d1d5db; padding: 10px; text-align: left; }';
        $html .= 'td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }';
        $html .= 'tr:nth-child(even) { background-color: #f9fafb; }';
        $html .= '</style>';
        $html .= '</head><body>';
        $html .= '<h2>' . htmlspecialchars($reportName) . '</h2>';
        $html .= '<p>Generated on: ' . now()->toDateTimeString() . '</p>';
        $html .= '<table><thead><tr>';
        
        foreach (array_values($columns) as $label) {
            $html .= '<th>' . htmlspecialchars($label) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach (array_keys($columns) as $key) {
                $val = $this->resolveValue($row, $key);
                $html .= '<td>' . htmlspecialchars((string) $val) . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table></body></html>';

        Storage::disk('public')->put($fileName, $html);

        return $fileName;
    }

    /**
     * Resolve nested relationship value.
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
