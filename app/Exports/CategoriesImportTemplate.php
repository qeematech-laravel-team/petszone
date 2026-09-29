<?php

namespace App\Exports;

use App\Models\Category;
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

class CategoriesImportTemplate implements FromCollection, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    protected Collection $categories;

    public function __construct()
    {
        // Get all categories for dropdown
        $this->categories = Category::query()->select(['id', 'name'])->get();
    }

    /**
     * Return empty collection (just template with headers)
     */
    public function collection(): Collection
    {
        return collect([
            // Example row (can be removed or kept as example)
            [

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
            'parent',
            'is_active',
            'is_featured',
            'image_url',
        ];
    }

    /**
     * Apply styles to the template
     */
    public function styles(Worksheet $sheet): void
    {
        // Style header row
        $sheet->getStyle('A1:F1')->applyFromArray([
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
            'C' => 30, // parent
            'D' => 15, // is_active
            'E' => 15, // is_featured
            'F' => 50, // image_url
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
                $highestRow = $sheet->getHighestRow();

                // 🔹 Categories dropdown — Column C (parent)
                $categories = Category::select('id', 'name')->get();
                $categoryList = $categories->map(function ($c) {
                    $name = $c->getTranslation('name', 'en', false) ?? '';
                    if (! empty($name)) {
                        return "{$c->id} ({$name})";
                    }

                    return null;
                })->filter()->implode(',');

                // Add "Root" option
                $categoryList = 'Root,'.$categoryList;

                for ($row = 2; $row <= 1000; $row++) {
                    $validation = $sheet->getCell("C{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"'.$categoryList.'"');
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

                // 🔹 is_featured dropdown — Column E (true/false)
                for ($row = 2; $row <= 1000; $row++) {
                    $validation = $sheet->getCell("E{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"true,false"');
                    $sheet->getCell("E{$row}")->setDataValidation($validation);
                }
            },
        ];
    }
}
