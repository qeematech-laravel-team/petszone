<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VariantsImportTemplate implements FromCollection, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    /**
     * Return empty collection (just template with headers)
     */
    public function collection(): Collection
    {
        return collect([
            // Example row
            [
                'name_en' => 'Color',
                'name_ar' => 'اللون',
                'is_required' => 'true',
                'is_active' => 'true',
                'options' => 'Red:أحمر:red|Blue:أزرق:blue|Green:أخضر:green',
            ],
        ]);
    }

    /**
     * Headers for the template
     */
    public function headings(): array
    {
        return [
            'name_en',
            'name_ar',
            'is_required',
            'is_active',
            'options',
        ];
    }

    /**
     * Apply styles to the template
     */
    public function styles(Worksheet $sheet): void
    {
        // Style header row
        $sheet->getStyle('A1:E1')->applyFromArray([
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
            'A' => 25, // name_en
            'B' => 25, // name_ar
            'C' => 15, // is_required
            'D' => 15, // is_active
            'E' => 80, // options
        ];
    }

    /**
     * Register events to add data validation after sheet is created
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // 🔹 is_required dropdown — Column C (true/false)
                for ($row = 2; $row <= 1000; $row++) {
                    $validation = $sheet->getCell("C{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"true,false"');
                    $sheet->getCell("C{$row}")->setDataValidation($validation);
                }

                // 🔹 is_active dropdown — Column D (true/false)
                for ($row = 2; $row <= 1000; $row++) {
                    $validation = $sheet->getCell("D{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"true,false"');
                    $sheet->getCell("D{$row}")->setDataValidation($validation);
                }
            },
        ];
    }
}
