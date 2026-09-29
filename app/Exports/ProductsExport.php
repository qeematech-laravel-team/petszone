<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromQuery, WithColumnWidths, WithHeadings, WithMapping, WithStyles
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
        $query = Product::query()
            ->with(['vendor', 'categories', 'images'])
            ->select([
                'id',
                'vendor_id',
                'type',
                'name',
                'description',
                'thumbnail',
                'sku',
                'slug',
                'price',
                'discount',
                'discount_type',
                'is_active',
                'is_featured',
                'is_new',
                'is_approved',
                'is_bookable',
                'created_at',
            ]);

        // Apply filters
        if (! empty($this->filters['search'])) {
            $search = trim($this->filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                    ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('is_active', $this->filters['status'] === 'active');
        }

        if (isset($this->filters['featured']) && $this->filters['featured'] !== '') {
            $query->where('is_featured', $this->filters['featured'] === '1');
        }

        if (isset($this->filters['approved']) && $this->filters['approved'] !== '') {
            $query->where('is_approved', $this->filters['approved'] === '1');
        }

        if (isset($this->filters['type']) && $this->filters['type'] !== '') {
            $query->where('type', $this->filters['type']);
        }

        if (isset($this->filters['vendor_id']) && $this->filters['vendor_id'] !== '') {
            $query->where('vendor_id', $this->filters['vendor_id']);
        }

        if (isset($this->filters['category_id']) && $this->filters['category_id'] !== '') {
            $query->whereHas('categories', function ($q) {
                $q->where('categories.id', $this->filters['category_id']);
            });
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
            'Vendor ID',
            'Type',
            'Name (EN)',
            'Name (AR)',
            'Description (EN)',
            'Description (AR)',
            'SKU',
            'Slug',
            'Price',
            'Discount',
            'Discount Type',
            'Thumbnail URL',
            'Image URLs',
            'Categories',
            'Is Active',
            'Is Featured',
            'Is New',
            'Is Approved',
            'Is Bookable',
            'Created At',
        ];
    }

    /**
     * Map each product to export row
     */
    public function map($product): array
    {
        $nameEn = $product->getTranslation('name', 'en', false);
        $nameAr = $product->getTranslation('name', 'ar', false);
        $descEn = $product->getTranslation('description', 'en', false);
        $descAr = $product->getTranslation('description', 'ar', false);

        // Format categories as comma-separated IDs
        $categories = '';
        if ($product->relationLoaded('categories') && $product->categories->isNotEmpty()) {
            $categories = $product->categories->pluck('name')->implode(',');
        }

        // Format images as comma-separated URLs
        $imageUrls = '';
        if ($product->relationLoaded('images') && $product->images->isNotEmpty()) {
            $imageUrls = $product->images->map(function ($image) {
                return $image->path ? asset('storage/'.$image->path) : '';
            })->filter()->implode(',');
        }

        // Get thumbnail URL
        $thumbnailUrl = '';
        if ($product->thumbnail) {
            $thumbnailUrl = $product->getOriginal('thumbnail')
                ? asset('storage/'.$product->getOriginal('thumbnail'))
                : '';
        }

        return [
            $product->id,
            $product->vendor_id,
            $product->type,
            $nameEn,
            $nameAr,
            $descEn,
            $descAr,
            $product->sku ?? '',
            $product->slug ?? '',
            $product->price ?? 0,
            $product->discount ?? 0,
            $product->discount_type ?? 'percentage',
            $thumbnailUrl,
            $imageUrls,
            $categories,
            $product->is_active ? 'true' : 'false',
            $product->is_featured ? 'true' : 'false',
            $product->is_new ? 'true' : 'false',
            $product->is_approved ? 'true' : 'false',
            $product->is_bookable ? 'true' : 'false',
            $product->created_at ? $product->created_at->format('Y-m-d H:i:s') : '',
        ];
    }

    /**
     * Apply styles
     */
    public function styles(Worksheet $sheet): void
    {
        // Style header row
        $sheet->getStyle('A1:U1')->applyFromArray([
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
            'B' => 12, // Vendor ID
            'C' => 12, // Type
            'D' => 30, // Name (EN)
            'E' => 30, // Name (AR)
            'F' => 40, // Description (EN)
            'G' => 40, // Description (AR)
            'H' => 20, // SKU
            'I' => 25, // Slug
            'J' => 12, // Price
            'K' => 12, // Discount
            'L' => 15, // Discount Type
            'M' => 50, // Thumbnail URL
            'N' => 50, // Image URLs
            'O' => 20, // Categories (IDs)
            'P' => 12, // Is Active
            'Q' => 12, // Is Featured
            'R' => 12, // Is New
            'S' => 12, // Is Approved
            'T' => 12, // Is Bookable
            'U' => 20, // Created At
        ];
    }
}
