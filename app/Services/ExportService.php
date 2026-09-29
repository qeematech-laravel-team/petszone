<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * Export collection to CSV format.
     */
    public function exportToCsv(Collection $data, array $headers, string $filename): StreamedResponse
    {
        $callback = function () use ($data, $headers) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8 (Excel compatibility)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Write headers
            fputcsv($file, $headers);

            // Write data rows
            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    /**
     * Export collection to Excel format (CSV with .xlsx extension).
     * Note: For full Excel support, install maatwebsite/excel package.
     */
    public function exportToExcel(Collection $data, array $headers, string $filename): StreamedResponse
    {
        // For now, we'll use CSV format but with .xlsx extension
        // In production, consider using maatwebsite/excel package
        return $this->exportToCsv($data, $headers, str_replace('.xlsx', '.csv', $filename));
    }
}
