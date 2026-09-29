<?php

namespace App\Exports;

use App\Models\Variant;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VariantsExport implements FromQuery, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Query to export
     */
    public function query(): Builder
    {
        $query = Variant::query()
            ->with('options')
            ->select([
                'id',
                'name',
                'is_required',
                'is_active',
                'created_at',
            ]);

        // Apply filters
        if (! empty($this->filters['search'])) {
            $search = trim($this->filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"]);
            });
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('is_active', $this->filters['status'] === 'active');
        }

        if (isset($this->filters['required']) && $this->filters['required'] !== '') {
            $query->where('is_required', $this->filters['required'] === '1');
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Headings
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name (EN)',
            'Name (AR)',
            'Is Required',
            'Is Active',
            'Options',
            'Created At',
        ];
    }

    /**
     * Map each variant to export row
     */
    public function map($variant): array
    {
        $locale = app()->getLocale();
        $nameEn = $variant->getTranslation('name', 'en', false);
        $nameAr = $variant->getTranslation('name', 'ar', false);

        // Format options: opt1_en:opt1_ar:opt1_code|opt2_en:opt2_ar:opt2_code
        $optionsString = '';
        if ($variant->relationLoaded('options') && $variant->options->isNotEmpty()) {
            $optionsArray = [];
            foreach ($variant->options as $option) {
                $optEn = $option->getTranslation('name', 'en', false);
                $optAr = $option->getTranslation('name', 'ar', false);
                $optCode = $option->code ?? '';
                $optionsArray[] = "{$optEn}:{$optAr}:{$optCode}";
            }
            $optionsString = implode('|', $optionsArray);
        }

        return [
            $variant->id,
            $nameEn,
            $nameAr,
            $variant->is_required ? 'true' : 'false',
            $variant->is_active ? 'true' : 'false',
            $optionsString,
            $variant->created_at ? $variant->created_at->format('Y-m-d H:i:s') : '',
        ];
    }

    /**
     * Apply styles
     */
    public function styles(Worksheet $sheet): void
    {
        // Style header row
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    /**
     * Set column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 10, // ID
            'B' => 25, // Name (EN)
            'C' => 25, // Name (AR)
            'D' => 15, // Is Required
            'E' => 15, // Is Active
            'F' => 50, // Options
            'G' => 20, // Created At
        ];
    }
}
