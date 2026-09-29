<?php

namespace App\Http\Controllers\Admin;

use App\Exports\VariantsExport;
use App\Exports\VariantsImportTemplate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Variants\CreateRequest;
use App\Http\Requests\Admin\Variants\UpdateRequest;
use App\Imports\VariantsImport;
use App\Models\Variant;
use App\Services\VariantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VariantController extends Controller
{
    protected VariantService $service;

    public function __construct(VariantService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of variants
     */
    public function index(): View|JsonResponse
    {
        $filters = [
            'search' => request()->get('search', ''),
            'status' => request()->get('status', ''),
            'required' => request()->get('required', ''),
        ];

        $variants = $this->service->getPaginatedVariants(15, $filters);

        // If AJAX request, return JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'variants' => $variants,
            ]);
        }

        return view('admin.variants.index', compact('variants', 'filters'));
    }

    /**
     * Show the form for creating a new variant
     */
    public function create(): View
    {
        return view('admin.variants.create');
    }

    /**
     * Store a newly created variant in storage
     */
    public function store(CreateRequest $request): RedirectResponse
    {
        try {
            $this->service->createVariant($request);

            return redirect()->route('admin.variants.index')
                ->with('success', __('Variant created successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('Failed to create variant: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Display the specified variant
     */
    public function show(Variant $variant): View
    {
        $variant = $this->service->getVariantById($variant->id);

        return view('admin.variants.show', compact('variant'));
    }

    /**
     * Show the form for editing the specified variant
     */
    public function edit(Variant $variant): View
    {
        $variant = $this->service->getVariantById($variant->id);

        return view('admin.variants.edit', compact('variant'));
    }

    /**
     * Update the specified variant in storage
     */
    public function update(UpdateRequest $request, Variant $variant): RedirectResponse
    {
        try {
            $this->service->updateVariant($request, $variant);

            return redirect()->route('admin.variants.index')
                ->with('success', __('Variant updated successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('Failed to update variant: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Remove the specified variant from storage
     */
    public function destroy(Variant $variant): RedirectResponse|JsonResponse
    {
        try {
            $this->service->deleteVariant($variant);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Variant deleted successfully.'),
                ]);
            }

            return redirect()->route('admin.variants.index')
                ->with('success', __('Variant deleted successfully.'));
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to delete variant: :error', ['error' => $e->getMessage()]),
                ], 422);
            }

            return redirect()->back()
                ->with('error', __('Failed to delete variant: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Toggle variant active status
     */
    public function toggleActive(Variant $variant): JsonResponse
    {
        try {
            $variant = $this->service->toggleActive($variant);

            return response()->json([
                'success' => true,
                'message' => __('Variant status updated successfully.'),
                'variant' => $variant,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update variant status: :error', ['error' => $e->getMessage()]),
            ], 422);
        }
    }

    /**
     * Toggle variant required status
     */
    public function toggleRequired(Variant $variant): JsonResponse
    {
        try {
            $variant = $this->service->toggleRequired($variant);

            return response()->json([
                'success' => true,
                'message' => __('Variant required status updated successfully.'),
                'variant' => $variant,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update variant required status: :error', ['error' => $e->getMessage()]),
            ], 422);
        }
    }

    /**
     * Export variants
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = [
            'search' => $request->get('search', ''),
            'status' => $request->get('status', ''),
            'required' => $request->get('required', ''),
        ];

        $filename = 'variants_export_'.date('Y-m-d_His').'.xlsx';

        return Excel::download(new VariantsExport($filters), $filename);
    }

    /**
     * Show import form
     */
    public function showImport(): View
    {
        return view('admin.variants.import');
    }

    /**
     * Handle variants import (queued)
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'], // 10MB max
        ]);

        try {
            // Store file temporarily
            $file = $request->file('file');
            $filePath = $file->store('imports', 'local');

            // Queue the import
            $userId = Auth::check() ? Auth::id() : null;
            $import = new VariantsImport($userId);
            Excel::queueImport($import, $filePath, 'local');

            return redirect()->route('admin.variants.index')
                ->with('success', __('Variants import has been queued and will be processed in the background. You will be notified when it completes.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to queue variants import: '.$e->getMessage());
        }
    }

    /**
     * Download import template
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        $filename = 'variants_import_template_'.date('Y-m-d').'.xlsx';

        return Excel::download(new VariantsImportTemplate, $filename);
    }
}
