<?php

namespace App\Services;

use App\Models\Plan;
use App\Repositories\PlanRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlanService
{
    protected PlanRepository $planRepository;

    public function __construct(PlanRepository $planRepository)
    {
        $this->planRepository = $planRepository;
    }

    /**
     * Get all plans
     */
    public function getAllPlans(): Collection
    {
        return $this->planRepository->getAllPlans();
    }

    /**
     * Get paginated plans
     */
    public function getPaginatedPlans(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->planRepository->getPaginatedPlans($perPage, $filters);
    }

    /**
     * Get plan by ID
     */
    public function getPlanById(int $id): ?Plan
    {
        return $this->planRepository->getPlanById($id);
    }

    /**
     * Get active plans
     */
    public function getActivePlans(): Collection
    {
        return $this->planRepository->getActivePlans();
    }

    /**
     * Get featured plans
     */
    public function getFeaturedPlans(): Collection
    {
        return $this->planRepository->getFeaturedPlans();
    }

    /**
     * Create a new plan
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function createPlan($request): Plan
    {
        DB::beginTransaction();
        try {
            $data = [
                'name' => $request->name,
                'description' => $request->description ?? [],
                'price' => $request->price,
                'duration_days' => $request->duration_days,
                'can_feature_products' => $request->boolean('can_feature_products', false),
                'max_products_count' => $request->max_products_count ?? null,
                'is_active' => $request->boolean('is_active', true),
                'is_featured' => $request->boolean('is_featured', false),
            ];

            // Generate slug from English name
            if (isset($request->name['en']) && ! empty($request->name['en'])) {
                $baseSlug = \Illuminate\Support\Str::slug($request->name['en']);
                $slug = $baseSlug;
                $counter = 1;

                // Ensure slug is unique
                while ($this->planRepository->getPlanBySlug($slug)) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $data['slug'] = $slug;
            }

            $plan = $this->planRepository->create($data);

            DB::commit();

            return $plan;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update a plan
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function updatePlan($request, Plan $plan): Plan
    {
        DB::beginTransaction();
        try {
            $data = [
                'name' => $request->name,
                'description' => $request->description ?? $plan->description,
                'price' => $request->price,
                'duration_days' => $request->duration_days,
                'can_feature_products' => $request->boolean('can_feature_products', false),
                'max_products_count' => $request->max_products_count ?? null,
                'is_active' => $request->boolean('is_active'),
                'is_featured' => $request->boolean('is_featured'),
            ];

            // Update slug if name changed
            if (isset($request->name['en']) && ! empty($request->name['en'])) {
                $newSlug = \Illuminate\Support\Str::slug($request->name['en']);
                // Only update slug if it's different and doesn't conflict with another plan
                if ($newSlug !== $plan->slug) {
                    // Check if slug already exists for another plan
                    $existingPlan = $this->planRepository->getPlanBySlug($newSlug);
                    if (! $existingPlan || $existingPlan->id === $plan->id) {
                        $data['slug'] = $newSlug;
                    } else {
                        // Append a number if slug exists
                        $counter = 1;
                        while ($this->planRepository->getPlanBySlug($newSlug.'-'.$counter)) {
                            $counter++;
                        }
                        $data['slug'] = $newSlug.'-'.$counter;
                    }
                }
            }

            $this->planRepository->update($plan, $data);
            $plan->refresh();

            DB::commit();

            return $plan;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a plan
     */
    public function deletePlan(Plan $plan): bool
    {
        DB::beginTransaction();
        try {

            $deleted = $this->planRepository->delete($plan);

            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Force delete a plan
     */
    public function forceDeletePlan(Plan $plan): bool
    {
        DB::beginTransaction();
        try {
            $deleted = $this->planRepository->forceDelete($plan);
            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Restore a soft deleted plan
     */
    public function restorePlan(Plan $plan): bool
    {
        return $this->planRepository->restore($plan);
    }

    /**
     * Search plans
     */
    public function searchPlans(string $search): Collection
    {
        return $this->planRepository->search($search);
    }

    /**
     * Toggle plan active status
     */
    public function toggleActive(Plan $plan): Plan
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        return $plan->fresh();
    }

    /**
     * Toggle plan featured status
     */
    public function toggleFeatured(Plan $plan): Plan
    {
        $plan->update(['is_featured' => ! $plan->is_featured]);

        return $plan->fresh();
    }

    // Subscribe to a plan
    public function subscribeToPlan(array $data)
    {
        DB::beginTransaction();
        try {
            $plan = $this->getPlanById($data['plan_id']);
            $user = Auth::user();
            $immediate = $data['immediate'] ?? true;

            if (! $user) {
                throw new \Exception('User not found');
            }

            if (! $user->hasRole('vendor') && ! $user->hasRole('vendor_employee')) {
                throw new \Exception('User is not a vendor');
            }

            $vendor = $user->vendor();

            if (! $vendor) {
                throw new \Exception('Vendor not found');
            }

            $current = $vendor->activeSubscription();

            // Already subscribed to the same plan → renew
            if ($current && $current->plan_id === $plan->id) {
                $current->end_date = \Carbon\Carbon::parse($current->end_date)->addDays($plan->duration_days);
                $current->status = 'active';
                $current->save();

                // Update vendor subscription dates
                $vendor->update([
                    'subscription_start' => $current->start_date,
                    'subscription_end' => $current->end_date,
                ]);

                DB::commit();

                return $current;
            }

            // Switching to a different plan
            if ($current) {
                $currentPlan = $current->plan;

                // Check if downgrading (new plan has fewer features or lower price)
                $isDowngrade = $this->isDowngrade($currentPlan, $plan);

                // For downgrade: Check constraints BEFORE switching
                if ($isDowngrade) {
                    // Check featured products
                    if (! $plan->can_feature_products && $vendor->products()->featured()->count() > 0) {
                        throw new \Exception(__('You have featured products. Please remove featured status from products before downgrading to this plan.'));
                    }

                    // Check max products count
                    if ($plan->max_products_count && $vendor->products()->active()->count() > $plan->max_products_count) {
                        throw new \Exception(__('You have :count active products. This plan allows maximum :max products. Please delete or deactivate some products first.', [
                            'count' => $vendor->products()->active()->count(),
                            'max' => $plan->max_products_count,
                        ]));
                    }
                }

                if ($immediate) {
                    // Immediate switch: Cancel current subscription
                    $current->status = 'inactive';
                    $current->save();

                    $startDate = now();
                    $endDate = now()->addDays($plan->duration_days);

                    // Apply downgrade restrictions immediately
                    if ($isDowngrade) {
                        // Remove featured products if not allowed
                        if (! $plan->can_feature_products) {
                            $vendor->products()->featured()->update(['is_featured' => false]);
                        }

                        // Deactivate excess products if over limit
                        if ($plan->max_products_count) {
                            $excessCount = $vendor->products()->active()->count() - $plan->max_products_count;
                            if ($excessCount > 0) {
                                $vendor->products()->active()
                                    ->latest()
                                    ->limit($excessCount)
                                    ->update(['is_active' => false]);
                            }
                        }
                    }
                } else {
                    // Schedule switch: Wait until current subscription ends
                    $startDate = \Carbon\Carbon::parse($current->end_date)->addDay();
                    $endDate = $startDate->copy()->addDays($plan->duration_days);

                    // Keep current subscription active until it ends
                    // The new subscription will be created but won't be active yet
                    // Note: You might want to add a 'pending' status for scheduled subscriptions
                }
            } else {
                // No current subscription: Start immediately
                $startDate = now();
                $endDate = now()->addDays($plan->duration_days);
            }

            // Update vendor plan info
            $vendor->update([
                'plan_id' => $plan->id,
                'subscription_start' => $startDate,
                'subscription_end' => $endDate,
            ]);

            // Create new subscription
            $subscription = $vendor->subscriptions()->create([
                'plan_id' => $plan->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'price' => $plan->getRawOriginal('price'),
                'status' => $immediate ? 'active' : 'inactive', // If scheduled, set to inactive
            ]);

            DB::commit();

            return $subscription;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if switching from current plan to new plan is a downgrade
     */
    private function isDowngrade(Plan $currentPlan, Plan $newPlan): bool
    {
        if ($newPlan->getRawOriginal('price') < $currentPlan->getRawOriginal('price')) {
            return true;
        }

        if ($currentPlan->can_feature_products && ! $newPlan->can_feature_products) {
            return true;
        }

        $currentMax = $currentPlan->max_products_count; // null = unlimited
        $newMax = $newPlan->max_products_count;

        if ($currentMax === null && $newMax !== null) {
            return true;
        }

        if ($currentMax !== null && $newMax !== null && $currentMax > $newMax) {
            return true;
        }

        return false;
    }
}
