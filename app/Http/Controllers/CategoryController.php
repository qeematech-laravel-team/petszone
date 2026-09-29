<?php

namespace App\Http\Controllers;

use App\Exports\CategoriesExport;
use App\Http\Requests\Admin\Categories\CreateRequest;
use App\Http\Requests\Admin\Categories\UpdateRequest;
use App\Imports\CategoriesImport;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CategoryController extends Controller
{
    protected CategoryService $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    public function index(): View|JsonResponse
    {
        $filters = [
            'search' => request()->get('search', ''),
            'status' => request()->get('status', ''),
            'featured' => request()->get('featured', ''),
            'parent_id' => request()->get('parent_id', ''),
        ];

        $categories = $this->service->getPaginatedCategories(15, $filters);

        // If AJAX request, return JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'html' => view('admin.categories.partials.table', compact('categories'))->render(),
                'pagination' => view('admin.categories.partials.pagination', compact('categories'))->render(),
            ]);
        }

        // Get all categories for parent filter dropdown
        $allCategories = $this->service->getCategoryTree();

        return view('admin.categories.index', compact('categories', 'allCategories', 'filters'));
    }

    public function create(): View
    {
        $allCategories = $this->service->getCategoryTree();

        return view('admin.categories.create', compact('allCategories'));
    }

    public function store(CreateRequest $request): RedirectResponse
    {
        try {
            $this->service->createCategory($request);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create category: '.$e->getMessage());
        }
    }

    public function show(Category $category): View
    {
        $category = $this->service->getCategoryById($category->id);

        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category): View
    {
        $category = $this->service->getCategoryById($category->id);
        $allCategories = $this->service->getCategoryTree()
            ->filter(fn ($cat) => $cat->id !== $category->id);

        return view('admin.categories.edit', compact('category', 'allCategories'));
    }

    public function update(UpdateRequest $request, Category $category): RedirectResponse
    {
        // try {
            $this->service->updateCategory($request, $category);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category updated successfully.');
        // } catch (\Exception $e) {
        //     return redirect()->back()
        //         ->withInput()
        //         ->with('error', 'Failed to update category: '.$e->getMessage());
        // }
    }

    public function destroy(Category $category): RedirectResponse|JsonResponse
    {
        try {
            $this->service->deleteCategory($category);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Category deleted successfully.'),
                ]);
            }

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to delete category: :error', ['error' => $e->getMessage()]),
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Failed to delete category: '.$e->getMessage());
        }
    }

    /**
     * Export categories to Excel
     * Optimized for performance using chunked processing
     */
    public function export(): BinaryFileResponse
    {
        // Get filters from request
        $filters = [
            'search' => request()->get('search', ''),
            'status' => request()->get('status', ''),
            'featured' => request()->get('featured', ''),
            'parent_id' => request()->get('parent_id', ''),
            'sort' => request()->get('sort', 'latest'),
        ];

        // Generate filename with timestamp
        $filename = 'categories_export_'.date('Y-m-d_His').'.xlsx';

        // Pass filters directly to export class for better performance
        // The export class will use FromQuery with chunking to process data efficiently
        return Excel::download(new CategoriesExport($filters), $filename);
    }

    /**
     * Show import form
     */
    public function showImport(): View
    {
        $allCategories = $this->service->getCategoryTree();

        return view('admin.categories.import', compact('allCategories'));
    }

    /**
     * Handle categories import (queued)
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
            $fileName = $file->getClientOriginalName();

            // Queue the import
            $userId = Auth::check() ? Auth::id() : null;
            $import = new CategoriesImport($userId);
            Excel::queueImport($import, $filePath, 'local');

            return redirect()->route('admin.categories.index')
                ->with('success', __('Categories import has been queued and will be processed in the background. You will be notified when it completes.'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to queue categories import: '.$e->getMessage());
        }
    }

    /**
     * Download import template
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        // Create a simple template with headers
        $template = new CategoriesExport([]);
        $filename = 'categories_import_template_'.date('Y-m-d').'.xlsx';

        // We'll create a template export that only has headers
        return Excel::download(new \App\Exports\CategoriesImportTemplate, $filename);
    }
}
