<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'stock' => $this->stock ?? 0,
            'price' => $this->price,
            'is_active' => $this->is_active ?? true,
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'values' => ProductVariantValueResource::collection($this->whenLoaded('values')),
        ];
    }
}
