<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorOrderResource extends JsonResource
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
            'order_id' => $this->order_id,
            'vendor_id' => $this->vendor_id,
            'branch_id' => $this->branch_id,
            'subtotal' => $this->subtotal ?? $this->sub_total,
            'discount' => $this->discount,
            'shipping_cost' => $this->shipping_cost,
            'total' => $this->total,
            'commission' => $this->commission,
            'status' => $this->status,
            'notes' => $this->notes,
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'items' => VendorOrderItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->when(
                $this->relationLoaded('items'),
                fn () => $this->items->count(),
                fn () => $this->items()->count()
            ),
        ];
    }
}
