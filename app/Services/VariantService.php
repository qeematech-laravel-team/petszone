<?php

namespace App\Services;

use App\Models\Variant;
use App\Models\VariantOption;
use App\Repositories\VariantRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VariantService
{
    protected VariantRepository $variantRepository;

    public function __construct(VariantRepository $variantRepository)
    {
        $this->variantRepository = $variantRepository;
    }

    /**
     * Get all variants
     */
    public function getAllVariants(): Collection
    {
        return $this->variantRepository->getAllVariants();
    }

    /**
     * Get paginated variants
     */
    public function getPaginatedVariants(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->variantRepository->getPaginatedVariants($perPage, $filters);
    }

    /**
     * Get variant by ID
     */
    public function getVariantById(int $id): ?Variant
    {
        return $this->variantRepository->getVariantById($id);
    }

    /**
     * Get active variants
     */
    public function getActiveVariants(): Collection
    {
        return $this->variantRepository->getActiveVariants();
    }

    /**
     * Get required variants
     */
    public function getRequiredVariants(): Collection
    {
        return $this->variantRepository->getRequiredVariants();
    }

    /**
     * Create a new variant with options
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function createVariant($request): Variant
    {
        DB::beginTransaction();
        try {
            $variantData = [
                'name' => $request->name,
                'is_required' => $request->boolean('is_required'),
                'is_active' => $request->boolean('is_active', true),
            ];

            $variant = $this->variantRepository->create($variantData);

            // Create variant options if provided
            if ($request->has('options') && is_array($request->options)) {
                $this->createVariantOptions($variant, $request->options);
            }

            DB::commit();

            return $variant->load('options');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update a variant and its options
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function updateVariant($request, Variant $variant): Variant
    {
        DB::beginTransaction();
        try {
            $variantData = [
                'name' => $request->name,
                'is_required' => $request->boolean('is_required'),
                'is_active' => $request->boolean('is_active'),
            ];

            $this->variantRepository->update($variant, $variantData);

            // Handle variant options
            if ($request->has('options')) {
                $this->syncVariantOptions($variant, $request->options);
            }

            DB::commit();

            return $variant->load('options');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a variant
     */
    public function deleteVariant(Variant $variant): bool
    {
        DB::beginTransaction();
        try {
            $deleted = $this->variantRepository->delete($variant);

            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Force delete a variant
     */
    public function forceDeleteVariant(Variant $variant): bool
    {
        DB::beginTransaction();
        try {
            $deleted = $this->variantRepository->forceDelete($variant);

            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Restore a soft deleted variant
     */
    public function restoreVariant(Variant $variant): bool
    {
        return $this->variantRepository->restore($variant);
    }

    /**
     * Search variants
     */
    public function searchVariants(string $search): Collection
    {
        return $this->variantRepository->search($search);
    }

    /**
     * Toggle variant active status
     */
    public function toggleActive(Variant $variant): Variant
    {
        $variant->update(['is_active' => !$variant->is_active]);
        return $variant->load('options');
    }

    /**
     * Toggle variant required status
     */
    public function toggleRequired(Variant $variant): Variant
    {
        $variant->update(['is_required' => !$variant->is_required]);
        return $variant->load('options');
    }

    /**
     * Create variant options
     */
    protected function createVariantOptions(Variant $variant, array $options): void
    {
        foreach ($options as $optionData) {
            if (!empty($optionData['name']['en']) || !empty($optionData['name']['ar'])) {
                $code = $optionData['code'] ?? $this->generateUniqueCode($optionData['name']['en'] ?? $optionData['name']['ar'] ?? 'option');
                
                VariantOption::create([
                    'variant_id' => $variant->id,
                    'name' => $optionData['name'],
                    'code' => $code,
                ]);
            }
        }
    }

    /**
     * Sync variant options (update existing, create new, delete removed)
     */
    protected function syncVariantOptions(Variant $variant, array $options): void
    {
        $existingOptionIds = [];
        
        foreach ($options as $optionData) {
            if (isset($optionData['id']) && !empty($optionData['id'])) {
                // Update existing option
                $existingOptionIds[] = $optionData['id'];
                $option = VariantOption::find($optionData['id']);
                
                if ($option && $option->variant_id === $variant->id) {
                    $updateData = [
                        'name' => $optionData['name'],
                    ];
                    
                    if (isset($optionData['code']) && !empty($optionData['code'])) {
                        $updateData['code'] = $optionData['code'];
                    }
                    
                    $option->update($updateData);
                }
            } else {
                // Create new option
                if (!empty($optionData['name']['en']) || !empty($optionData['name']['ar'])) {
                    $code = $optionData['code'] ?? $this->generateUniqueCode($optionData['name']['en'] ?? $optionData['name']['ar'] ?? 'option');
                    
                    VariantOption::create([
                        'variant_id' => $variant->id,
                        'name' => $optionData['name'],
                        'code' => $code,
                    ]);
                }
            }
        }

        // Delete options that were removed
        $variant->options()->whereNotIn('id', $existingOptionIds)->delete();
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
            $code = $baseCode . '-' . $counter;
            $counter++;
        }

        return $code;
    }
}
