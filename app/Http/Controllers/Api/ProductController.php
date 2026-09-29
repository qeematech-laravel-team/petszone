<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $perPage = request()->get('per_page', 15);
        $filters = [
            'search' => request()->get('search', ''),
            'featured' => request()->get('featured', ''),
            'vendor_id' => request()->get('vendor_id', ''),
            'category_id' => request()->get('category_id', ''),
            'min_price' => request()->get('min_price', ''),
            'max_price' => request()->get('max_price', ''),
            'stock' => request()->get('stock', ''),
            'sort' => request()->get('sort', ''),
            'approved' => 1,
            'status' => 'active',
        ];
        $products = $this->service->getPaginatedProducts($perPage, $filters);

        return ProductResource::collection($products);
    }

    public function show($id)
    {
        $product = $this->service->getApiProductById((int) $id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => __('Product not found.'),
            ], 404);
        }

        return new ProductResource($product);
    }

    public function toggleFavorite(Product $product)
    {
        $productId = $product->id;
        $user = auth()->user();

        if ($user->favoriteProducts()->where('product_id', $productId)->exists()) {
            $user->favoriteProducts()->detach($productId);

            return response()->json(['message' => 'Removed from favorites']);
        } else {
            $user->favoriteProducts()->attach($productId);

            return response()->json(['message' => 'Added to favorites']);
        }
    }

    public function favoriteList()
    {
        $user = auth()->user();
        $favorites = $user->favoriteProducts()
            ->active()
            ->with(Product::apiListRelations())
            ->get()
            ->each(fn (Product $product) => $product->setAttribute('is_favorited', true));

        return ProductResource::collection($favorites);
    }

    // public function search(Request $request)
    // {
    //     // search for products by name or sku
    //     $query = $request->input('query');
    //     $products = $this->service->searchApi($query);
    //     return ProductResource::collection($products);
    // }
}
