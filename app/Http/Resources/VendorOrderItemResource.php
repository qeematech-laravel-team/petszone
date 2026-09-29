<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorOrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_order_id' => $this->vendor_order_id,
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'total' => $this->total,
            'notes' => $this->notes,
            'product' => new ProductResource($this->whenLoaded('product')),
            'variant' => new ProductVariantResource($this->whenLoaded('variant')),
        ];
    }
}
