<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\VendorRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class VendorService
{
    protected VendorRepository $vendorRepository;

    public function __construct(VendorRepository $vendorRepository)
    {
        $this->vendorRepository = $vendorRepository;
    }

    /**
     * Get all vendors
     */
    public function getAllVendors(): Collection
    {
        return $this->vendorRepository->getAllVendors();
    }

    /**
     * Get paginated vendors
     */
    public function getPaginatedVendors(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->vendorRepository->getPaginatedVendors($perPage, $filters);
    }

    /**
     * Get vendor by ID
     */
    public function getVendorById(int $id, bool $activeProductsOnly = false): ?Vendor
    {
        return $this->vendorRepository->getVendorById($id, $activeProductsOnly);
    }

    /**
     * Get vendor by slug
     */
    public function getVendorBySlug(string $slug): ?Vendor
    {
        return $this->vendorRepository->getVendorBySlug($slug);
    }

    /**
     * Get active vendors
     */
    public function getActiveVendors(): Collection
    {
        return $this->vendorRepository->getActiveVendors();
    }

    /**
     * Get featured vendors
     */
    public function getFeaturedVendors(): Collection
    {
        return $this->vendorRepository->getFeaturedVendors();
    }

    /**
     * Create a new vendor (by admin)
     */
    public function createVendor($request): Vendor
    {
        DB::beginTransaction();
        try {
            // Create owner user first
            $owner = User::create([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'phone' => $request->phone,
                'password' => Hash::make($request->owner_password),
                'is_active' => true,
                'is_verified' => false,
                'role' => 'vendor',
            ]);

            // Assign vendor role if exists
            if (\Spatie\Permission\Models\Role::where('name', 'vendor')->exists()) {
                $owner->assignRole('vendor');
            }

            $data = [
                'name' => $request->name,
                'owner_id' => $owner->id,
                'phone' => $request->phone,
                'address' => $request->address,
                'is_active' => $request->boolean('is_active', true),
                'is_featured' => $request->boolean('is_featured', false),
                'balance' => $request->balance ?? 0,
                'commission_rate' => $request->commission_rate ?? 0,
                'plan_id' => $request->plan_id ?? null,
                'subscription_start' => $request->subscription_start ?? null,
                'subscription_end' => $request->subscription_end ?? null,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('vendors', 'public');
            }

            // Generate slug from English name
            if (isset($request->name['en']) && ! empty($request->name['en'])) {
                $baseSlug = \Illuminate\Support\Str::slug($request->name['en']);
                $slug = $baseSlug;
                $counter = 1;

                // Ensure slug is unique
                while ($this->vendorRepository->getVendorBySlug($slug)) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $data['slug'] = $slug;
            }

            $vendor = $this->vendorRepository->create($data);

            DB::commit();

            return $vendor;
        } catch (\Exception $e) {
            DB::rollBack();
            // If an image was uploaded, delete it
            if (isset($data['image']) && Storage::disk('public')->exists($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }
            throw $e;
        }
    }

    /**
     * Register a new vendor (self-registration)
     */
    public function registerVendor($request): Vendor
    {
        DB::beginTransaction();
        try {
            // Check if user already has a vendor
            if ($this->vendorRepository->ownerHasVendor($request->user()->id)) {
                throw new \Exception('You already have a vendor account.');
            }

            // Create or get user account
            $user = $request->user();
            if (! $user) {
                // Create new user if not authenticated
                $user = User::create([
                    'name' => $request->owner_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'is_active' => true,
                    'is_verified' => false,
                    'role' => 'vendor',
                ]);

                // Assign vendor role if exists
                if (\Spatie\Permission\Models\Role::where('name', 'vendor')->exists()) {
                    $user->assignRole('vendor');
                }
            }

            $data = [
                'name' => $request->name,
                'owner_id' => $user->id,
                'phone' => $request->phone ?? $user->phone,
                'address' => $request->address,
                'is_active' => false, // New vendors need admin approval
                'is_featured' => false,
                'balance' => 0,
                'commission_rate' => setting('profit_value', 0) ?? 0,
                'plan_id' => $request->plan_id ?? null,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('vendors', 'public');
            }

            // Generate slug from English name
            if (isset($request->name['en']) && ! empty($request->name['en'])) {
                $baseSlug = \Illuminate\Support\Str::slug($request->name['en']);
                $slug = $baseSlug;
                $counter = 1;

                // Ensure slug is unique
                while ($this->vendorRepository->getVendorBySlug($slug)) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $data['slug'] = $slug;
            }

            $vendor = $this->vendorRepository->create($data);

            DB::commit();

            return $vendor;
        } catch (\Exception $e) {
            DB::rollBack();
            // If an image was uploaded, delete it
            if (isset($data['image']) && Storage::disk('public')->exists($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }
            throw $e;
        }
    }

    /**
     * Update a vendor
     */
    public function updateVendor($request, Vendor $vendor): Vendor
    {
        DB::beginTransaction();
        try {
            // Update or create owner user information
            $ownerId = $vendor->owner_id;

            if ($vendor->owner) {
                // Update existing owner
                $ownerData = [
                    'name' => $request->owner_name,
                    'email' => $request->owner_email,
                ];

                // Update password only if provided
                if ($request->filled('owner_password')) {
                    $ownerData['password'] = Hash::make($request->owner_password);
                }

                $vendor->owner->update($ownerData);
            } else {
                // Create new owner if vendor doesn't have one
                $owner = User::create([
                    'name' => $request->owner_name,
                    'email' => $request->owner_email,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->owner_password ?? 'password'),
                    'is_active' => true,
                    'is_verified' => false,
                    'role' => 'vendor',
                ]);

                // Assign vendor role if exists
                if (\Spatie\Permission\Models\Role::where('name', 'vendor')->exists()) {
                    $owner->assignRole('vendor');
                }

                $ownerId = $owner->id;
            }

            $data = [
                'name' => $request->name,
                'owner_id' => $ownerId,
                'phone' => $request->phone,
                'address' => $request->address,
                'is_active' => $request->boolean('is_active'),
                'is_featured' => $request->boolean('is_featured'),
                'balance' => $request->balance ?? $vendor->balance,
                'commission_rate' => $request->commission_rate ?? $vendor->commission_rate,
                'plan_id' => $request->plan_id ?? $vendor->plan_id,
                'subscription_start' => $request->subscription_start ?? $vendor->subscription_start,
                'subscription_end' => $request->subscription_end ?? $vendor->subscription_end,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                // dd($vendor->id,$vendor->image, $vendor->getRawOriginal('image'));
                // Delete old image if exists
                if ($vendor->getRawOriginal('image') && Storage::disk('public')->exists($vendor->getRawOriginal('image'))) {
                    Storage::disk('public')->delete($vendor->getRawOriginal('image'));
                }
                $data['image'] = $request->file('image')->store('vendors', 'public');
            } elseif (isset($request->image_removed) && $request->image_removed) {
                // If image was explicitly removed
                if ($vendor->image && Storage::disk('public')->exists($vendor->getRawOriginal('image'))) {
                    Storage::disk('public')->delete($vendor->getRawOriginal('image'));
                }
                $data['image'] = null;
            }

            // Update slug if name changed
            if (isset($request->name['en']) && ! empty($request->name['en'])) {
                $newSlug = \Illuminate\Support\Str::slug($request->name['en']);
                // Only update slug if it's different and doesn't conflict with another vendor
                if ($newSlug !== $vendor->slug) {
                    $existingVendor = $this->vendorRepository->getVendorBySlug($newSlug);
                    if (! $existingVendor || $existingVendor->id === $vendor->id) {
                        $data['slug'] = $newSlug;
                    } else {
                        // Append a number if slug exists
                        $counter = 1;
                        while ($this->vendorRepository->getVendorBySlug($newSlug.'-'.$counter)) {
                            $counter++;
                        }
                        $data['slug'] = $newSlug.'-'.$counter;
                    }
                }
            }

            $this->vendorRepository->update($vendor, $data);
            $vendor->refresh();

            DB::commit();

            return $vendor;
        } catch (\Exception $e) {
            DB::rollBack();
            // If a new image was uploaded during this transaction, delete it on rollback
            if (isset($data['image']) && $request->hasFile('image') && Storage::disk('public')->exists($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }
            throw $e;
        }
    }

    /**
     * Delete a vendor (soft delete)
     */
    public function deleteVendor(Vendor $vendor): bool
    {
        DB::beginTransaction();
        try {
            // Delete image if exists
            if ($vendor->image && Storage::disk('public')->exists($vendor->getRawOriginal('image'))) {
                Storage::disk('public')->delete($vendor->getRawOriginal('image'));
            }

            $deleted = $this->vendorRepository->delete($vendor);

            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Force delete a vendor
     */
    public function forceDeleteVendor(Vendor $vendor): bool
    {
        DB::beginTransaction();
        try {
            // Delete image if exists
            if ($vendor->image && Storage::disk('public')->exists($vendor->getRawOriginal('image'))) {
                Storage::disk('public')->delete($vendor->getRawOriginal('image'));
            }

            $deleted = $this->vendorRepository->forceDelete($vendor);

            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Restore a soft deleted vendor
     */
    public function restoreVendor(Vendor $vendor): bool
    {
        return $this->vendorRepository->restore($vendor);
    }

    /**
     * Search vendors
     */
    public function searchVendors(string $search): Collection
    {
        return $this->vendorRepository->search($search);
    }

    /**
     * Toggle vendor active status
     */
    public function toggleActive(Vendor $vendor): Vendor
    {
        $vendor->update(['is_active' => ! $vendor->is_active]);

        return $vendor->fresh();
    }

    /**
     * Toggle vendor featured status
     */
    public function toggleFeatured(Vendor $vendor): Vendor
    {
        $vendor->update(['is_featured' => ! $vendor->is_featured]);

        return $vendor->fresh();
    }

    /**
     * Assign plan to vendor
     */
    public function assignPlan(Vendor $vendor, Plan $plan, ?string $startDate = null, ?string $endDate = null): Vendor
    {
        $durationDays = $plan->duration_days;
        $start = $startDate ? \Carbon\Carbon::parse($startDate) : \Carbon\Carbon::now();
        $end = $endDate ? \Carbon\Carbon::parse($endDate) : $start->copy()->addDays($durationDays);

        $vendor->update([
            'plan_id' => $plan->id,
            'subscription_start' => $start->format('Y-m-d'),
            'subscription_end' => $end->format('Y-m-d'),
        ]);

        return $vendor->fresh();
    }
}
