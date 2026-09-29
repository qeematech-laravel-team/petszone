<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\Vendor;
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

class ProductsImportTemplate implements FromCollection, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    protected Collection $categories;

    public function __construct()
    {
        // Get all categories for reference list
        $this->categories = Category::query()->select(['id', 'name'])->get();
    }

    /**
     * Return empty collection (just template with headers)
     */
    public function collection(): Collection
    {
        return collect([
            // Example row
            [
                'vendor_id' => '1',
                'type' => 'simple',
                'name_en' => 'Example Product',
                'name_ar' => 'منتج مثال',
                'description_en' => 'Product description in English',
                'description_ar' => 'وصف المنتج بالعربية',
                'sku' => 'PRD-EXAMPLE-001',
                'slug' => 'example-product',
                'price' => '100.00',
                'discount' => '10',
                'discount_type' => 'percentage',
                'thumbnail_url' => 'https://example.com/thumbnail.jpg',
                'image_urls' => 'https://example.com/image1.jpg,https://example.com/image2.jpg',
                'categories' => '1,2,3',
                'categories_list' => '', // Reference column (dropdown will be populated)
                'is_active' => 'true',
                'is_featured' => 'false',
                'is_new' => 'true',
                'is_approved' => 'false',
                'is_bookable' => 'false',
            ],
        ]);
    }

    /**
     * Headers for the template
     */
    public function headings(): array
    {
        return [
            'vendor_id',
            'type',
            'name_en',
            'name_ar',
            'description_en',
            'description_ar',
            'sku',
            'slug',
            'price',
            'discount',
            'discount_type',
            'thumbnail_url',
            'image_urls',
            'categories',
            'categories_list', // Reference column showing available categories
            'is_active',
            'is_featured',
            'is_new',
            'is_approved',
            'is_bookable',
        ];
    }

    /**
     * Apply styles to the template
     */
    public function styles(Worksheet $sheet): void
    {
        // Style header row
        $sheet->getStyle('A1:T1')->applyFromArray([
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

        // Style categories_list column (Column O) - make it visually distinct as reference
        $sheet->getStyle('O:O')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8F4F8'], // Light blue background
            ],
            'font' => ['italic' => true, 'color' => ['rgb' => '666666']],
        ]);
    }

    /**
     * Set column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15, // vendor_id
            'B' => 12, // type
            'C' => 30, // name_en
            'D' => 30, // name_ar
            'E' => 40, // description_en
            'F' => 40, // description_ar
            'G' => 20, // sku
            'H' => 25, // slug
            'I' => 12, // price
            'J' => 12, // discount
            'K' => 15, // discount_type
            'L' => 50, // thumbnail_url
            'M' => 50, // image_urls
            'N' => 20, // categories
            'O' => 80, // categories_list (reference column)
            'P' => 12, // is_active
            'Q' => 12, // is_featured
            'R' => 12, // is_new
            'S' => 12, // is_approved
            'T' => 12, // is_bookable
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

                // 🔹 Vendors dropdown — Column A (vendor_id)
                $vendors = Vendor::select('id', 'name')->get();
                $vendorList = $vendors->map(function ($v) {
                    $name = $v->getTranslation('name', 'en', false);
                    if (! empty($name)) {
                        return "{$v->id} ({$name})";
                    }

                    return null;
                })->filter()->implode(',');

                for ($row = 2; $row <= 1000; $row++) {
                    $validation = $sheet->getCell("A{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"'.$vendorList.'"');
                    $sheet->getCell("A{$row}")->setDataValidation($validation);
                }

                // 🔹 Type dropdown — Column B (simple/variable)
                for ($row = 2; $row <= 1000; $row++) {
                    $validation = $sheet->getCell("B{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"simple,variable"');
                    $sheet->getCell("B{$row}")->setDataValidation($validation);
                }

                // 🔹 Discount Type dropdown — Column K (percentage/fixed)
                for ($row = 2; $row <= 1000; $row++) {
                    $validation = $sheet->getCell("K{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"percentage,fixed"');
                    $sheet->getCell("K{$row}")->setDataValidation($validation);
                }

                // 🔹 Categories List dropdown — Column O (categories_list) - Reference column
                $categoriesList = $this->categories->map(function ($category) {
                    $name = $category->getTranslation('name', 'en', false);
                    if (! empty($name)) {
                        return "{$category->id} ({$name})";
                    }

                    return null;
                })->filter()->implode(',');

                for ($row = 2; $row <= 1000; $row++) {
                    $validation = $sheet->getCell("O{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"'.$categoriesList.'"');
                    $sheet->getCell("O{$row}")->setDataValidation($validation);
                }

                // 🔹 Boolean dropdowns — Columns P-T (true/false)
                $booleanColumns = ['P' => 'is_active', 'Q' => 'is_featured', 'R' => 'is_new', 'S' => 'is_approved', 'T' => 'is_bookable'];
                foreach ($booleanColumns as $col => $name) {
                    for ($row = 2; $row <= 1000; $row++) {
                        $validation = $sheet->getCell("{$col}{$row}")->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_STOP);
                        $validation->setAllowBlank(true);
                        $validation->setShowDropDown(true);
                        $validation->setFormula1('"true,false"');
                        $sheet->getCell("{$col}{$row}")->setDataValidation($validation);
                    }
                }
            },
        ];
    }
}
