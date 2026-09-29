<?php

namespace App\Repositories;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class CartRepository
{
    public function __construct(protected CartItem $model) {}

    public function getUserCartItems(int $userId): Collection
    {
        return $this->model->with([
            'product' => fn ($query) => Product::constrainApiVisible(
                $query->with(Product::apiListRelations())
            ),
            'variant',
        ])
            ->where('user_id', $userId)
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->get();
    }

    public function addOrIncrement(int $userId, Product $product, ?int $variantId = null): CartItem
    {
        if ($variantId) {
            $cartItem = $this->model->firstOrCreate([
                'user_id' => $userId,
                'product_id' => $product->id,
                'variant_id' => $variantId,
            ]);
        } else {
            $cartItem = $this->model->firstOrCreate([
                'user_id' => $userId,
                'product_id' => $product->id,
            ]);
        }

        if (! $cartItem->wasRecentlyCreated) {
            $cartItem->increment('quantity');
        } else {
            $cartItem->quantity = 1;
            $cartItem->save();
        }

        return $cartItem;
    }

    public function updateQuantity(int $userId, Product $product, int $quantity, ?int $variantId = null): CartItem
    {
        if ($variantId) {
            $cartItem = $this->model->where('user_id', $userId)->where('product_id', $product->id)->where('variant_id', $variantId)->firstOrFail();
        } else {
            $cartItem = $this->model->where('user_id', $userId)->where('product_id', $product->id)->firstOrFail();
        }
        $cartItem->update(['quantity' => $quantity]);

        return $cartItem;
    }

    public function removeItem(int $userId, Product $product, ?int $variantId = null): int
    {
        if ($variantId) {
            return $this->model->where('user_id', $userId)->where('product_id', $product->id)->where('variant_id', $variantId)->delete();
        } else {
            return $this->model->where('user_id', $userId)->where('product_id', $product->id)->delete();
        }
    }

    public function clearCart(int $userId): int
    {
        return $this->model->where('user_id', $userId)->delete();
    }
}
