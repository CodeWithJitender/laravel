<?php

namespace App\Services\Export;

class ExportServiceManager
{
    protected CsvExportService $csvService;
    protected ExcelExportService $excelService;
    protected PdfExportService $pdfService;

    public function __construct(
        CsvExportService $csvService,
        ExcelExportService $excelService,
        PdfExportService $pdfService
    ) {
        $this->csvService = $csvService;
        $this->excelService = $excelService;
        $this->pdfService = $pdfService;
    }

    /**
     * Export collection to the given format.
     */
    public function export(string $reportName, array $columns, $data, string $format): string
    {
        switch (strtolower($format)) {
            case 'csv':
                return $this->csvService->generate($reportName, $columns, $data);
            case 'xlsx':
            case 'xls':
                return $this->excelService->generate($reportName, $columns, $data);
            case 'pdf':
                return $this->pdfService->generate($reportName, $columns, $data);
            default:
                throw new \Exception("Unsupported export format: {$format}");
        }
    }
}
