<?php

namespace App\Factories;

use App\Contracts\ProductPriceStockInterface;
use App\Models\Product;
use App\Services\ProductTypes\SimpleProduct;
use App\Services\ProductTypes\VariableProduct;

class ProductPriceStockFactory
{
    public static function make(Product $product, ?int $branchId = null): ProductPriceStockInterface
    {
        if ($product->type === 'variable') {
            return new VariableProduct($product, $branchId);
        }
        if ($product->type === 'simple') {
            return new SimpleProduct($product, $branchId);
        }
        throw new \InvalidArgumentException('Invalid product type');
    }
}
