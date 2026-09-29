<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Vendor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;

class ProductsImport implements ShouldQueue, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, ToModel, WithBatchInserts, WithChunkReading, WithEvents, WithHeadingRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    protected array $vendorsById = [];

    protected array $vendorsByName = [];

    protected int $rowCount = 0;

    protected ?int $userId = null;

    protected ?string $filePath = null;

    public function __construct(?int $userId = null, ?string $filePath = null)
    {
        $this->userId = $userId;
        $this->filePath = $filePath;

        // Load vendors for lookup
        $vendors = Vendor::query()->select(['id', 'name'])->get();

        foreach ($vendors as $vendor) {
            $this->vendorsById[$vendor->id] = $vendor;

            $nameData = $vendor->name ?? [];
            $name = is_array($nameData) ? ($nameData['en'] ?? '') : '';
            $name = strtolower(trim($name));
            if (! empty($name)) {
                $this->vendorsByName[$name] = $vendor;
            }
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row): ?Product
    {
        // Log BEFORE incrementing to see if method is called
        Log::channel('single')->info('model() called - Processing row', [
            'row_data' => $row,
            'row_keys' => array_keys($row),
            'user_id' => $this->userId,
        ]);

        $this->rowCount++;

        // Log the import with context - use channel('single') to ensure it writes to file
        Log::channel('single')->info('Importing product', [
            'row_number' => $this->rowCount,
            'row_data' => $row,
            'user_id' => $this->userId,
        ]);

        // Prepare name and description arrays
        $name = [
            'en' => trim($row['name_en'] ?? ''),
            'ar' => trim($row['name_ar'] ?? ''),
        ];

        $description = [
            'en' => trim($row['description_en'] ?? ''),
            'ar' => trim($row['description_ar'] ?? ''),
        ];

        // Find vendor
        $vendorId = $this->findVendorId($row['vendor_id'] ?? '');

        if (! $vendorId) {
            Log::warning('Vendor not found during import', [
                'vendor_id' => $row['vendor_id'] ?? '',
                'row' => $row,
            ]);
            throw new \Exception('Vendor not found. Please provide a valid vendor ID or name.');
        }

        // Generate SKU if not provided
        $sku = ! empty($row['sku']) ? trim($row['sku']) : $this->generateUniqueSku();

        // Generate slug if not provided
        $slug = ! empty($row['slug']) ? trim($row['slug']) : $this->generateSlug($name['en'] ?: $name['ar'] ?: 'product');

        // Create product
        $product = new Product([
            'vendor_id' => $vendorId,
            'type' => $row['type'] ?? 'simple',
            'name' => $name,
            'description' => $description,
            'sku' => $sku,
            'slug' => $slug,
            'price' => (float) ($row['price'] ?? 0),
            'discount' => (float) ($row['discount'] ?? 0),
            'discount_type' => $row['discount_type'] ?? 'percentage',
            'is_active' => $this->convertToBoolean($row['is_active'] ?? true),
            'is_featured' => $this->convertToBoolean($row['is_featured'] ?? false),
            'is_new' => $this->convertToBoolean($row['is_new'] ?? false),
            'is_approved' => $this->convertToBoolean($row['is_approved'] ?? false),
            'is_bookable' => $this->convertToBoolean($row['is_bookable'] ?? false),
        ]);

        // Save product first to get ID
        $product->save();

        // Handle thumbnail
        if (! empty($row['thumbnail_url'])) {
            $thumbnailPath = $this->handleImageUrl($row['thumbnail_url']);
            if ($thumbnailPath) {
                $product->update(['thumbnail' => $thumbnailPath]);
            }
        }

        // Handle categories (comma-separated IDs or names)
        if (! empty($row['categories'])) {
            $categoryIds = $this->parseCategories($row['categories']);
            if (! empty($categoryIds)) {
                $product->categories()->sync($categoryIds);
            }
        }

        // Handle images (comma-separated URLs)
        if (! empty($row['image_urls'])) {
            $this->handleProductImages($product, $row['image_urls']);
        }

        $this->rowCount++;

        return $product;
    }

    /**
     * Find vendor ID from vendor_id column (supports ID or name)
     */
    protected function findVendorId($vendorValue): ?int
    {
        if (empty($vendorValue)) {
            return null;
        }

        $vendorValue = trim($vendorValue);

        // If numeric, try to find by ID
        if (is_numeric($vendorValue)) {
            $vendorId = (int) $vendorValue;
            if (isset($this->vendorsById[$vendorId])) {
                return $vendorId;
            }
        }

        // Try to find by name (case-insensitive)
        $vendorName = strtolower($vendorValue);
        if (isset($this->vendorsByName[$vendorName])) {
            return $this->vendorsByName[$vendorName]->id;
        }

        return null;
    }

    /**
     * Parse categories - supports IDs (comma-separated) or names (comma-separated)
     */
    protected function parseCategories(string $categoriesString): array
    {
        if (empty($categoriesString)) {
            return [];
        }

        $items = array_map('trim', explode(',', $categoriesString));
        $categoryIds = [];

        // Load categories for lookup
        $categories = \App\Models\Category::query()->select(['id', 'name'])->get();
        $categoriesByName = [];

        foreach ($categories as $category) {
            $nameData = $category->name ?? [];
            $name = is_array($nameData) ? ($nameData['en'] ?? '') : '';
            $name = strtolower(trim($name));
            if (! empty($name)) {
                $categoriesByName[$name] = $category;
            }
        }

        foreach ($items as $item) {
            if (empty($item)) {
                continue;
            }

            // If numeric, treat as ID
            if (is_numeric($item)) {
                $categoryId = (int) $item;
                if (\App\Models\Category::where('id', $categoryId)->exists()) {
                    $categoryIds[] = $categoryId;
                }
            } else {
                // Try to find by name (case-insensitive)
                $itemName = strtolower($item);
                if (isset($categoriesByName[$itemName])) {
                    $categoryIds[] = $categoriesByName[$itemName]->id;
                }
            }
        }

        return array_unique($categoryIds);
    }

    /**
     * Handle product images from comma-separated URLs
     */
    protected function handleProductImages(Product $product, string $imageUrls): void
    {
        if (empty($imageUrls)) {
            return;
        }

        $urls = array_map('trim', explode(',', $imageUrls));
        Log::info('Image URLs: '.json_encode($urls));
        foreach ($urls as $url) {
            if (empty($url)) {
                continue;
            }

            try {
                $imagePath = $this->handleImageUrl($url);
                if ($imagePath) {
                    ProductImage::create([
                        'imageable_type' => Product::class,
                        'imageable_id' => $product->id,
                        'path' => $imagePath,
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning("Failed to download product image from URL: {$url}", ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Handle image URL - download and store locally
     */
    protected function handleImageUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        try {
            // If it's already a local path (starts with products/)
            if (Str::startsWith($url, 'products/')) {
                if (Storage::disk('public')->exists($url)) {
                    return $url;
                }
            }

            // If it's a relative path, try to use it directly
            if (Str::startsWith($url, '/') || ! Str::contains($url, '://')) {
                $path = ltrim($url, '/');
                if (Storage::disk('public')->exists($path)) {
                    return $path;
                }
            }

            // If it's a full URL, download it
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                try {
                    // Reduced timeout to prevent blocking
                    $response = Http::timeout(15)->get($url);

                    // Check if response is successful (status 200-299)
                    if ($response->status() >= 200 && $response->status() < 300) {
                        $imageContent = $response->body();
                        $extension = $this->getImageExtensionFromUrl($url, $imageContent);
                        $filename = 'products/'.Str::random(40).'.'.$extension;

                        Storage::disk('public')->put($filename, $imageContent);

                        return $filename;
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to download image from URL: {$url}", ['error' => $e->getMessage()]);
                }
            }
        } catch (\Exception $e) {
            Log::warning("Failed to process image URL: {$url}", ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Get image extension from URL or content
     */
    protected function getImageExtensionFromUrl(string $url, string $content): string
    {
        // Try to get extension from URL
        $urlExtension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        if (in_array(strtolower($urlExtension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
            return strtolower($urlExtension);
        }

        // Try to detect from content
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $content);
        finfo_close($finfo);

        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
        ];

        return $mimeToExt[$mimeType] ?? 'jpg';
    }

    /**
     * Generate unique SKU
     */
    protected function generateUniqueSku(): string
    {
        $sku = 'PRD-'.strtoupper(Str::random(8));
        $counter = 1;

        while (Product::where('sku', $sku)->exists()) {
            $sku = 'PRD-'.strtoupper(Str::random(8)).'-'.$counter;
            $counter++;
        }

        return $sku;
    }

    /**
     * Generate unique slug
     */
    protected function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $baseSlug = $slug;
        $counter = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the number of rows imported
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'vendor_id' => ['required'],
            'type' => ['required', 'string', 'in:simple,variable'],
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'sku' => ['nullable', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'thumbnail_url' => ['nullable', 'string'],
            'image_urls' => ['nullable', 'string'],
            'categories' => ['nullable', 'string'],
            'is_active' => ['nullable'],
            'is_featured' => ['nullable'],
            'is_new' => ['nullable'],
            'is_approved' => ['nullable'],
            'is_bookable' => ['nullable'],
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages(): array
    {
        return [
            'vendor_id.required' => 'Vendor ID is required.',
            'type.required' => 'Product type is required.',
            'type.in' => 'Product type must be simple or variable.',
            'name_en.required' => 'Name (EN) is required.',
            'name_en.string' => 'Name (EN) must be a string.',
            'name_en.max' => 'Name (EN) must not exceed 255 characters.',
            'name_ar.string' => 'Name (AR) must be a string.',
            'name_ar.max' => 'Name (AR) must not exceed 255 characters.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be at least 0.',
            'discount.numeric' => 'Discount must be a number.',
            'discount.min' => 'Discount must be at least 0.',
            'discount_type.in' => 'Discount type must be percentage or fixed.',
        ];
    }

    /**
     * Process in batches for better performance
     */
    public function batchSize(): int
    {
        return 250;
    }

    /**
     * Process in chunks
     */
    public function chunkSize(): int
    {
        return 250;
    }

    /**
     * Convert boolean value to boolean
     */
    protected function convertToBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower(trim($value));

            return $value === 'true';
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        return false;
    }

    /**
     * Register events for queue processing
     */
    /**
     * Prepare the row for validation
     */
    public function prepareForValidation($data, $index): array
    {
        Log::channel('single')->info('prepareForValidation called', [
            'index' => $index,
            'data' => $data,
            'data_keys' => array_keys($data),
            'user_id' => $this->userId,
        ]);

        // Convert numeric strings to proper types for validation
        if (isset($data['price'])) {
            if (is_string($data['price'])) {
                $data['price'] = (float) $data['price'];
            } elseif (is_numeric($data['price'])) {
                $data['price'] = (float) $data['price'];
            }
        }
        if (isset($data['discount'])) {
            if (is_string($data['discount'])) {
                $data['discount'] = (float) $data['discount'];
            } elseif (is_numeric($data['discount'])) {
                $data['discount'] = (float) $data['discount'];
            }
        }
        // Keep vendor_id as is - it can be string or integer
        if (isset($data['vendor_id']) && !is_string($data['vendor_id']) && !is_numeric($data['vendor_id'])) {
            $data['vendor_id'] = (string) $data['vendor_id'];
        }

        Log::channel('single')->info('prepareForValidation - After conversion', [
            'index' => $index,
            'data' => $data,
            'user_id' => $this->userId,
        ]);

        return $data;
    }

    /**
     * Handle validation failure
     */
    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            Log::channel('single')->error('Validation failure', [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
                'user_id' => $this->userId,
            ]);
        }
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                try {
                    // Read all rows from the file using the file path
                    if ($this->filePath && Storage::disk('local')->exists($this->filePath)) {
                        $fullPath = Storage::disk('local')->path($this->filePath);

                        // Use PhpSpreadsheet to read all rows
                        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($fullPath);
                        $reader->setReadDataOnly(true);
                        $spreadsheet = $reader->load($fullPath);
                        $worksheet = $spreadsheet->getActiveSheet();
                        $rows = $worksheet->toArray();

                        // Get header row (first row)
                        $headers = !empty($rows) ? array_shift($rows) : [];

                        // Convert rows to associative arrays using headers
                        $dataRows = [];
                        foreach ($rows as $row) {
                            if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                                continue; // Skip empty rows
                            }
                            $associativeRow = [];
                            foreach ($headers as $index => $header) {
                                $associativeRow[$header] = $row[$index] ?? null;
                            }
                            $dataRows[] = $associativeRow;
                        }

                        Log::channel('single')->info('Products import started - All rows', [
                            'user_id' => $this->userId,
                            'total_rows' => count($dataRows),
                            'headers' => $headers,
                            'all_rows' => $dataRows,
                        ]);
                    } else {
                        Log::channel('single')->warning('File path not available for logging all rows', [
                            'user_id' => $this->userId,
                            'file_path' => $this->filePath,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::channel('single')->warning('Failed to log all rows at import start', [
                        'user_id' => $this->userId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            },
            AfterImport::class => function (AfterImport $event) {
                $importedCount = $this->rowCount;
                $failures = $this->failures();
                $errors = $this->errors();

                $message = "Successfully imported {$importedCount} products.";

                if ($failures->count() > 0) {
                    $message .= " {$failures->count()} row(s) failed.";
                }

                if (count($errors) > 0) {
                    $message .= ' '.count($errors).' error(s) occurred.';
                }

                Log::info('Products import completed', [
                    'user_id' => $this->userId,
                    'imported_count' => $importedCount,
                    'failures_count' => $failures->count(),
                    'errors_count' => count($errors),
                    'failures' => $failures->toArray(),
                    'errors' => $errors,
                ]);
            },
            ImportFailed::class => function (ImportFailed $event) {
                Log::error('Products import failed', [
                    'user_id' => $this->userId,
                    'exception' => $event->getException()->getMessage(),
                ]);
            },
        ];
    }
}
