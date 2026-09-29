<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use Illuminate\View\View;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of active categories for vendors
     */
    public function index(): View
    {
        
        // Get active categories only (vendors can only see active categories)
        $categories = $this->categoryService->getActiveCategories();

        return view('vendor.categories.index', compact('categories'));
    }
}
