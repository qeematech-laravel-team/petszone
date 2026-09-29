<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $product = $this->product;
        $variant = $this->variant;
        $quantity = (int) $this->quantity;

        $unitPrice = (float) ($variant?->price ?? $product->price);

        $discount = (float) ($product->discount ?? 0);
        $discountType = $product->discount_type;

        if ($discount > 0) {
            if ($discountType === 'percentage') {
                $unitPrice -= ($unitPrice * $discount) / 100;
            } else {
                $unitPrice = (float) max(0, $unitPrice - $discount);
            }
        }

        return [
            'id' => $this->id,
            'product' => new ProductResource($product),
            'variant' => $variant ? new ProductVariantResource($variant) : null,
            'quantity' => $quantity,
            'discount' => $product->discount,
            'discount_type' => $product->discount_type,
            'subtotal' => $quantity * $unitPrice,
        ];
    }
}
