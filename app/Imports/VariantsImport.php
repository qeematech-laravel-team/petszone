<?php

namespace App\Imports;

use App\Models\Variant;
use App\Models\VariantOption;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
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

class VariantsImport implements ShouldQueue, SkipsEmptyRows, SkipsOnError, SkipsOnFailure, ToModel, WithBatchInserts, WithChunkReading, WithEvents, WithHeadingRow, WithValidation
{
    use Importable, SkipsErrors, SkipsFailures;

    protected int $rowCount = 0;

    protected ?int $userId = null;

    public function __construct(?int $userId = null)
    {
        $this->userId = $userId;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row): ?Variant
    {
        // Prepare name array
        $name = [
            'en' => trim($row['name_en'] ?? ''),
            'ar' => trim($row['name_ar'] ?? ''),
        ];

        // Convert boolean values
        $isRequired = $this->convertToBoolean($row['is_required'] ?? false);
        $isActive = $this->convertToBoolean($row['is_active'] ?? true);

        // Create variant
        $variant = new Variant([
            'name' => $name,
            'is_required' => $isRequired,
            'is_active' => $isActive,
        ]);

        // Save variant first to get ID
        $variant->save();

        // Parse and create options
        if (! empty($row['options'])) {
            $this->createVariantOptions($variant, $row['options']);
        }

        $this->rowCount++;

        return $variant;
    }

    /**
     * Parse options string and create variant options
     * Format: opt1_en:opt1_ar:opt1_code|opt2_en:opt2_ar:opt2_code
     */
    protected function createVariantOptions(Variant $variant, string $optionsString): void
    {
        if (empty($optionsString)) {
            return;
        }

        // Split by | to get individual options
        $options = explode('|', $optionsString);

        foreach ($options as $optionString) {
            $optionString = trim($optionString);
            if (empty($optionString)) {
                continue;
            }

            // Split by : to get en_name:ar_name:code
            $parts = explode(':', $optionString);

            if (count($parts) >= 2) {
                $optEn = trim($parts[0] ?? '');
                $optAr = trim($parts[1] ?? '');
                $optCode = trim($parts[2] ?? '');

                // Generate code if not provided
                if (empty($optCode)) {
                    $optCode = $this->generateUniqueCode($optEn ?: $optAr ?: 'option');
                }

                // Only create if at least one name is provided
                if (! empty($optEn) || ! empty($optAr)) {
                    VariantOption::create([
                        'variant_id' => $variant->id,
                        'name' => [
                            'en' => $optEn,
                            'ar' => $optAr,
                        ],
                        'code' => $optCode,
                    ]);
                }
            }
        }
    }

    /**
     * Generate unique code for variant option
     */
    protected function generateUniqueCode(string $name): string
    {
        $code = Str::slug($name);
        $baseCode = $code;
        $counter = 1;

        while (VariantOption::where('code', $code)->exists()) {
            $code = $baseCode.'-'.$counter;
            $counter++;
        }

        return $code;
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
            'is_required' => ['nullable'],
            'is_active' => ['nullable'],
            'options' => ['nullable', 'string'],
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
            'options.string' => 'Options must be a string.',
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
    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                $importedCount = $this->rowCount;
                $failures = $this->failures();
                $errors = $this->errors();

                $message = "Successfully imported {$importedCount} variants.";

                if ($failures->count() > 0) {
                    $message .= " {$failures->count()} row(s) failed.";
                }

                if (count($errors) > 0) {
                    $message .= ' '.count($errors).' error(s) occurred.';
                }

                Log::info('Variants import completed', [
                    'user_id' => $this->userId,
                    'imported_count' => $importedCount,
                    'failures_count' => $failures->count(),
                    'errors_count' => count($errors),
                ]);
            },
            ImportFailed::class => function (ImportFailed $event) {
                Log::error('Variants import failed', [
                    'user_id' => $this->userId,
                    'exception' => $event->getException()->getMessage(),
                ]);
            },
        ];
    }
}
