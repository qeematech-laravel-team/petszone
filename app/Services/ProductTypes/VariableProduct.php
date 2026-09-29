<?php

namespace App\Services\ProductTypes;

use App\Contracts\ProductPriceStockInterface;
use App\Models\BranchProductVariantStock;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class VariableProduct implements ProductPriceStockInterface
{
    public function __construct(private Product $product, private ?int $branchId = null) {}

    public function price()
    {
        if ($this->branchId) {
            $minPrice = null;

            foreach ($this->variants() as $variant) {
                $branchStock = $this->branchStockFor($variant);

                $variantPrice = ($branchStock && $branchStock->price !== null) ? $branchStock->price : $variant->price;

                if ($minPrice === null || $variantPrice < $minPrice) {
                    $minPrice = $variantPrice;
                }
            }

            if ($minPrice !== null) {
                return $minPrice;
            }
        }

        // Fall back to variant default prices
        return $this->variants()->min('price');
    }

    public function stock()
    {
        $variants = $this->variants();

        if ($variants->isEmpty()) {
            return 0;
        }

        $totalStock = 0;

        foreach ($variants as $variant) {
            $branchStocks = $this->branchStocksFor($variant);

            if ($branchStocks->isNotEmpty()) {
                $totalStock += $branchStocks->sum('quantity');
            } else {
                // Fall back to variant stock field if no branch stocks for this variant
                $totalStock += $variant->stock ?? 0;
            }
        }

        return $totalStock;
    }

    public function finalPrice()
    {
        // Get the minimum variant price (base price for variable products)
        $minVariantPrice = $this->price();

        // For variable products, we need to check branch prices for each variant from stock records
        // and find the minimum final price after discounts
        if ($this->branchId) {
            $minFinalPrice = null;

            foreach ($this->variants() as $variant) {
                $branchStock = $this->branchStockFor($variant);

                $basePrice = ($branchStock && $branchStock->price !== null) ? $branchStock->price : $variant->price;
                $finalPrice = $basePrice;

                // Apply branch-specific discount if exists
                if ($branchStock && $branchStock->hasDiscount()) {
                    if ($branchStock->discount_type === 'percentage') {
                        $finalPrice = $basePrice - (($basePrice * $branchStock->discount) / 100);
                    } else {
                        $finalPrice = $basePrice - $branchStock->discount;
                    }
                    $finalPrice = max(0, $finalPrice);
                } elseif ($variant->discount && $variant->discount > 0) {
                    // Fall back to variant-level discount
                    if ($variant->discount_type === 'percentage') {
                        $finalPrice = $basePrice - (($basePrice * $variant->discount) / 100);
                    } else {
                        $finalPrice = $basePrice - $variant->discount;
                    }
                    $finalPrice = max(0, $finalPrice);
                }

                if ($minFinalPrice === null || $finalPrice < $minFinalPrice) {
                    $minFinalPrice = $finalPrice;
                }
            }

            if ($minFinalPrice !== null) {
                return (float) $minFinalPrice;
            }
        }

        // Fall back to product-level discount
        if (! $this->product->discount || $this->product->discount <= 0) {
            return (float) $minVariantPrice;
        }

        // Apply product-level discount to the minimum variant price
        if ($this->product->discount_type === 'percentage') {
            $discountedPrice = $minVariantPrice - (($minVariantPrice * $this->product->discount) / 100);

            return (float) max(0, $discountedPrice);
        }

        // Fixed discount
        return (float) max(0, $minVariantPrice - $this->product->discount);
    }

    /**
     * Get the product variants, preferring the eager-loaded relation to avoid N+1 queries.
     */
    private function variants(): Collection
    {
        if ($this->product->relationLoaded('variants')) {
            return $this->product->variants;
        }

        return $this->product->variants()->with('branchVariantStocks')->get();
    }

    /**
     * Get branch stocks for a variant, preferring the eager-loaded relation.
     */
    private function branchStocksFor(ProductVariant $variant): Collection
    {
        if ($variant->relationLoaded('branchVariantStocks')) {
            return $variant->branchVariantStocks;
        }

        return $variant->branchVariantStocks()->get();
    }

    /**
     * Get the stock record of a variant for the current branch, if any.
     */
    private function branchStockFor(ProductVariant $variant): ?BranchProductVariantStock
    {
        return $this->branchStocksFor($variant)->firstWhere('branch_id', $this->branchId);
    }
}
