<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Plans\CreateRequest;
use App\Http\Requests\Admin\Plans\UpdateRequest;
use App\Models\Plan;
use App\Services\PlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    protected PlanService $service;

    public function __construct(PlanService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): View|JsonResponse
    {
        $filters = [
            'search' => $request->get('search', ''),
            'status' => $request->get('status', ''),
            'featured' => $request->get('featured', ''),
        ];

        $plans = $this->service->getPaginatedPlans(15, $filters);

        // If AJAX request, return JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'html' => view('admin.plans.partials.table', compact('plans'))->render(),
                'pagination' => view('admin.plans.partials.pagination', compact('plans'))->render(),
            ]);
        }

        return view('admin.plans.index', compact('plans', 'filters'));
    }

    public function create(): View
    {
        return view('admin.plans.create');
    }

    public function store(CreateRequest $request): RedirectResponse
    {

        try {
            $this->service->createPlan($request);

            return redirect()->route('plans.index')
                ->with('success', 'Plan created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create plan: '.$e->getMessage());
        }
    }

    public function show(Plan $plan): View
    {
        $plan = $this->service->getPlanById($plan->id);

        return view('admin.plans.show', compact('plan'));
    }

    public function edit(Plan $plan): View
    {
        $plan = $this->service->getPlanById($plan->id);

        return view('admin.plans.edit', compact('plan'));
    }

    public function update(UpdateRequest $request, Plan $plan): RedirectResponse
    {
        try {
            $this->service->updatePlan($request, $plan);

            return redirect()->route('plans.index')
                ->with('success', 'Plan updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update plan: '.$e->getMessage());
        }
    }

    public function destroy(Plan $plan): RedirectResponse|JsonResponse
    {
        try {
            $this->service->deletePlan($plan);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Plan deleted successfully.'),
                ]);
            }

            return redirect()->route('plans.index')
                ->with('success', 'Plan deleted successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to delete plan: :error', ['error' => $e->getMessage()]),
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Failed to delete plan: '.$e->getMessage());
        }
    }

    public function vendorIndex(): View
    {
        $plans = $this->service->getPaginatedPlans(15, []);

        return view('vendor.plans.index', compact('plans'));
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'immediate' => 'sometimes|boolean',
        ]);

        try {
            $subscription = $this->service->subscribeToPlan([
                'plan_id' => $request->plan_id,
                'immediate' => $request->boolean('immediate', true), // Default to true if not provided
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Subscribed successfully to :plan', ['plan' => $subscription->plan->name]),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function check(Request $request)
    {
        $plan = Plan::findOrFail($request->plan_id);
        $vendor = auth()->user()->vendor();

        $featured_count = $vendor->products()->featured()->count();
        $current_products = $vendor->products()->active()->count();

        // Check if this is a downgrade
        $isDowngrade = false;
        $currentSubscription = $vendor->activeSubscription();
        if ($currentSubscription && $currentSubscription->plan) {
            $currentPlan = $currentSubscription->plan;

            // Compare by price
            if ($plan->getRawOriginal('price') < $currentPlan->getRawOriginal('price')) {
                $isDowngrade = true;
            }

            // Compare by features
            if ($currentPlan->can_feature_products && ! $plan->can_feature_products) {
                $isDowngrade = true;
            }

            // Compare product limits
            $currentMax = $currentPlan->max_products_count;
            $newMax = $plan->max_products_count;

            if ($currentMax === null && $newMax !== null) {
                $isDowngrade = true;
            }

            if ($currentMax !== null && $newMax !== null && $currentMax > $newMax) {
                $isDowngrade = true;
            }
        }

        return response()->json([
            'can_feature_products' => $plan->can_feature_products,
            'max_products_count' => $plan->max_products_count,
            'featured_count' => $featured_count,
            'current_products' => $current_products,
            'is_downgrade' => $isDowngrade,
            'has_active_subscription' => $currentSubscription !== null,
        ]);
    }
}
