<?php

namespace App\Exports;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CategoriesExport implements FromQuery, WithChunkReading, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Use query builder instead of collection for better performance
     * This processes data in chunks, reducing memory usage
     */
    public function query(): Builder
    {
        $query = Category::query()
            ->with('parent:id,name') // Only load parent id and name
            ->with(['children', 'products']) // Use count instead of loading full relations
            ->select([
                'id',
                'name',
                'slug',
                'image',
                'is_active',
                'is_featured',
                'parent_id',
                'created_at',
                'updated_at',
            ]);

        // Apply search filter
        if (! empty($this->filters['search'])) {
            $search = trim($this->filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"]);
            });
        }

        // Apply status filter
        if (isset($this->filters['status']) && $this->filters['status'] != '') {
            $query->where('is_active', $this->filters['status'] == 'active');
        }

        // Apply featured filter
        if (isset($this->filters['featured']) && $this->filters['featured'] != '') {
            $query->where('is_featured', $this->filters['featured'] == 1);
        }

        // Apply parent filter
        if (isset($this->filters['parent_id']) && $this->filters['parent_id'] != '') {
            if ($this->filters['parent_id'] == 'root') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $this->filters['parent_id']);
            }
        }

        // Sorting
        $sort = (string) ($this->filters['sort'] ?? 'latest');

        match ($sort) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderByRaw("JSON_EXTRACT(name, '$.en') ASC"),
            'name_desc' => $query->orderByRaw("JSON_EXTRACT(name, '$.en') DESC"),
            default => $query->latest(),
        };

        return $query;
    }

    /**
     * Process data in chunks to reduce memory usage
     * Adjust chunk size based on your server's memory limit
     */
    public function chunkSize(): int
    {
        return 500; // Process 500 records at a time
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name (EN)',
            'Name (AR)',
            'Parent Category',
            'Status',
            'Featured',
            'Subcategories Count',
            'Products Count',
            'Image URL',
            'Created At',
            'Updated At',
        ];
    }

    /**
     * @param  Category  $category
     */
    public function map($category): array
    {
        // dd($category->children->count());
        $locale = app()->getLocale();

        return [
            $category->id,
            $category->getTranslation('name', 'en', false) ?? '',
            $category->getTranslation('name', 'ar', false) ?? '',
            $category->parent ? $category->parent->getTranslation('name', $locale, false) ?? 'Root' : 'Root',
            $category->is_active ? 'Active' : 'Inactive',
            $category->is_featured ? 'Yes' : 'No',
            $category->children->count(), // Use withCount result
            $category->products->count(), // Use withCount result
            $category->image ?? '',
            $category->created_at ? $category->created_at->format('Y-m-d H:i:s') : '',
            $category->updated_at ? $category->updated_at->format('Y-m-d H:i:s') : '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Style the first row as bold text with background color
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,  // ID
            'B' => 25,  // Name (EN)
            'C' => 25,  // Name (AR)
            'D' => 20,  // Parent Category
            'E' => 12,  // Status
            'F' => 12,  // Featured
            'G' => 18,  // Subcategories Count
            'H' => 15,  // Products Count
            'I' => 40,  // Image URL
            'J' => 20,  // Created At
            'K' => 20,  // Updated At
        ];
    }
}
