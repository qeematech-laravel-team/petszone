<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'user_id' => $this->user_id,
            'coupon_id' => $this->coupon_id,
            'address_id' => $this->address_id,
            'sub_total' => $this->sub_total,
            'order_discount' => $this->order_discount,
            'coupon_discount' => $this->coupon_discount,
            'total_shipping' => $this->total_shipping,
            'points_discount' => $this->points_discount,
            'wallet_used' => $this->wallet_used,
            'total' => $this->total,
            'total_commission' => $this->total_commission,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'notes' => $this->notes,
            'user' => new UserResource($this->whenLoaded('user')),
            'coupon' => $this->whenLoaded('coupon'),
            'address' => new AddressResource($this->whenLoaded('address')),
            'vendor_orders' => VendorOrderResource::collection($this->whenLoaded('vendorOrders')),
            'vendor_orders_count' => $this->when(
                $this->relationLoaded('vendorOrders'),
                fn () => $this->vendorOrders->count(),
                fn () => $this->vendorOrders()->count()
            ),
        ];
    }
}
