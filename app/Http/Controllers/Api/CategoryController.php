<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    protected CategoryService $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $perPage = request()->get('per_page', 15);
        $filters = [
            'search' => request()->get('search', ''),
            'status' => request()->get('status', ''),
            'featured' => request()->get('featured', ''),
            'parent_id' => request()->get('parent_id', ''),
            'sort' => request()->get('sort', ''),
        ];
        $categories = $this->service->getPaginatedCategories($perPage, $filters);

        return CategoryResource::collection($categories);
    }

    public function show(Category $category)
    {
        $category = $this->service->getCategoryById($category->id, activeProductsOnly: true);

        return new CategoryResource($category);
    }
}
