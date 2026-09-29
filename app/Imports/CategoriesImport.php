<?php

namespace App\Imports;

use App\Models\Category;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
use Maatwebsite\Excel\Events\ImportFailed;

class CategoriesImport implements ShouldQueue, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, ToModel, WithBatchInserts, WithChunkReading, WithEvents, WithHeadingRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    protected Collection $categories;

    protected array $categoriesById = [];

    protected array $categoriesByName = [];

    protected int $rowCount = 0;

    protected ?int $userId = null;

    public function __construct(?int $userId = null)
    {
        $this->userId = $userId;
        // Load only needed columns for parent lookup (optimized)
        $this->categories = Category::query()
            ->select(['id', 'name'])
            ->get();

        // Create lookup arrays for faster access
        foreach ($this->categories as $category) {
            // Index by ID
            $this->categoriesById[$category->id] = $category;

            // Index by name (case-insensitive)
            // Note: name is stored as JSON array, get English translation
            $nameData = $category->name ?? [];
            $name = is_array($nameData) ? ($nameData['en'] ?? '') : '';
            $name = strtolower(trim($name));
            if (! empty($name)) {
                $this->categoriesByName[$name] = $category;
            }
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row): ?Category
    {
        // Prepare name array
        $name = [
            'en' => trim($row['name_en'] ?? ''),
            'ar' => trim($row['name_ar'] ?? ''),
        ];

        // Find parent category (optimized with array lookup)
        // Supports formats: "1 (Category Name)", "Category Name", or ID only
        $parentId = null;
        if (! empty($row['parent'])) {
            $parentValue = trim($row['parent']);

            // Check if it's "Root"
            if (strtolower($parentValue) === 'root') {
                $parentId = null;
            } elseif (preg_match('/^(\d+)\s*\(/', $parentValue, $matches)) {
                // Format: "1 (Category Name)" - extract ID
                $parentId = (int) $matches[1];
                // Verify ID exists
                if (! isset($this->categoriesById[$parentId])) {
                    $parentId = null;
                }
            } elseif (is_numeric($parentValue)) {
                // Just ID number
                $parentId = (int) $parentValue;
                // Verify ID exists
                if (! isset($this->categoriesById[$parentId])) {
                    $parentId = null;
                }
            } else {
                // Try to find by name (case-insensitive) - O(1) lookup
                $parentName = strtolower($parentValue);
                if (isset($this->categoriesByName[$parentName])) {
                    $parentId = $this->categoriesByName[$parentName]->id;
                }
            }
        }

        // Handle image URL
        $imagePath = null;
        if (! empty($row['image_url'])) {
            $imagePath = $this->handleImageUrl($row['image_url']);
        }

        // Convert boolean values
        $isActive = $this->convertToBoolean($row['is_active'] ?? true);
        $isFeatured = $this->convertToBoolean($row['is_featured'] ?? false);

        $this->rowCount++;

        return new Category([
            'name' => $name,
            'parent_id' => $parentId,
            'is_active' => $isActive,
            'is_featured' => $isFeatured,
            'image' => $imagePath,
        ]);
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
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'parent' => ['nullable', 'string'],
            'is_active' => ['nullable'],
            'is_featured' => ['nullable'],
            'image_url' => ['nullable', 'string', 'url'],
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages(): array
    {
        return [
            'name_en.required' => 'Name (EN) is required.',
            'name_en.string' => 'Name (EN) must be a string.',
            'name_en.max' => 'Name (EN) must not exceed 255 characters.',
            'name_ar.string' => 'Name (AR) must be a string.',
            'name_ar.max' => 'Name (AR) must not exceed 255 characters.',
            'parent.string' => 'Parent must be a string.',
            'image_url.url' => 'Image URL must be a valid URL.',
        ];
    }

    /**
     * Process in batches for better performance
     * Increased from 100 to 250 for better performance (adjust based on server capacity)
     */
    public function batchSize(): int
    {
        return 250;
    }

    /**
     * Process in chunks
     * Increased from 100 to 250 for better performance
     */
    public function chunkSize(): int
    {
        return 250;
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
            // If it's already a local path (starts with categories/)
            if (Str::startsWith($url, 'categories/')) {
                // Check if file exists
                if (Storage::disk('public')->exists($url)) {
                    return $url;
                }
            }

            // If it's a full URL, download it
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                try {
                    // Reduced timeout to prevent blocking
                    $response = Http::timeout(5)->get($url);

                    if ($response->successful()) {
                        $imageContent = $response->body();
                        $extension = $this->getImageExtensionFromUrl($url, $imageContent);
                        $filename = 'categories/'.Str::random(40).'.'.$extension;

                        Storage::disk('public')->put($filename, $imageContent);

                        return $filename;
                    }
                } catch (\Exception $e) {
                    // Log error but don't fail the import
                    Log::warning("Failed to download image from URL: {$url}", ['error' => $e->getMessage()]);
                }
            }

            // If it's a relative path, try to use it directly
            if (Str::startsWith($url, '/') || ! Str::contains($url, '://')) {
                $path = ltrim($url, '/');
                if (Storage::disk('public')->exists($path)) {
                    return $path;
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail the import
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
     * Convert boolean value to boolean
     * Now only accepts true/false (as per template dropdown)
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

        // For numeric values, treat 1 as true, 0 as false
        if (is_numeric($value)) {
            return (bool) $value;
        }

        return false;
    }

    /**
     * Register events for queue processing
     */
    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                // Import completed successfully
                $importedCount = $this->rowCount;
                $failures = $this->failures();
                $errors = $this->errors();

                $message = "Successfully imported {$importedCount} categories.";

                if ($failures->count() > 0) {
                    $message .= " {$failures->count()} row(s) failed.";
                }

                if (count($errors) > 0) {
                    $message .= ' '.count($errors).' error(s) occurred.';
                }

                // Log success
                Log::info('Categories import completed', [
                    'user_id' => $this->userId,
                    'imported_count' => $importedCount,
                    'failures_count' => $failures->count(),
                    'errors_count' => count($errors),
                ]);

                // TODO: Send notification to user if needed
                // You can use Laravel notifications here
            },
            ImportFailed::class => function (ImportFailed $event) {
                // Import failed
                Log::error('Categories import failed', [
                    'user_id' => $this->userId,
                    'exception' => $event->getException()->getMessage(),
                ]);

                // TODO: Send failure notification to user
            },
        ];
    }
}
