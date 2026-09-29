<?php

namespace App\Services\ProductTypes;

use App\Contracts\ProductPriceStockInterface;
use App\Models\BranchProductStock;
use App\Models\Product;
use Illuminate\Support\Collection;

class SimpleProduct implements ProductPriceStockInterface
{
    public function __construct(private Product $product, private ?int $branchId = null) {}

    public function price()
    {
        if ($this->branchId) {
            $branchStock = $this->branchStock();

            if ($branchStock && $branchStock->price !== null) {
                return $branchStock->price;
            }
        }

        // Fall back to product default price
        return $this->product->price;
    }

    public function stock()
    {
        $branchStocks = $this->branchStocks();

        if ($branchStocks->isNotEmpty()) {
            return $branchStocks->sum('quantity');
        }

        // Otherwise, fall back to the product's stock field
        return $this->product->stock ?? 0;
    }

    public function finalPrice()
    {
        $basePrice = $this->price();

        if ($this->branchId) {
            $branchStock = $this->branchStock();

            if ($branchStock && $branchStock->hasDiscount()) {
                if ($branchStock->discount_type === 'percentage') {
                    $discountedPrice = $basePrice - (($basePrice * $branchStock->discount) / 100);

                    return (float) max(0, $discountedPrice);
                }

                return (float) max(0, $basePrice - $branchStock->discount);
            }
        }

        // Fall back to product-level discount
        if (! $this->product->discount || $this->product->discount <= 0) {
            return (float) $basePrice;
        }
        if ($this->product->discount_type === 'percentage') {
            $discountedPrice = $basePrice - (($basePrice * $this->product->discount) / 100);

            return (float) max(0, $discountedPrice);
        }

        return (float) max(0, $basePrice - $this->product->discount);
    }

    /**
     * Get branch product stocks, preferring the eager-loaded relation to avoid N+1 queries.
     */
    private function branchStocks(): Collection
    {
        if ($this->product->relationLoaded('branchProductStocks')) {
            return $this->product->branchProductStocks;
        }

        return $this->product->branchProductStocks()->get();
    }

    /**
     * Get the stock record for the current branch, if any.
     */
    private function branchStock(): ?BranchProductStock
    {
        return $this->branchStocks()->firstWhere('branch_id', $this->branchId);
    }
}
