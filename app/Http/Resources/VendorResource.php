<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
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
            'name' => $this->name,
            'image' => $this->image,
            'phone' => $this->phone,
            'address' => $this->address,
            'rating' => [
                'average' => $this->visibleRatingAverage(),
                'count' => $this->visibleRatingCount(),
            ],
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'branches' => BranchResource::collection($this->whenLoaded('branches')),
        ];
    }

    private function visibleRatingAverage(): float
    {
        if (array_key_exists('visible_ratings_avg', $this->resource->getAttributes())) {
            return (float) ($this->visible_ratings_avg ?? 0);
        }

        if ($this->relationLoaded('ratings')) {
            return (float) ($this->ratings->where('is_visible', true)->avg('rating') ?? 0);
        }

        return (float) ($this->ratings()->where('is_visible', true)->avg('rating') ?? 0);
    }

    private function visibleRatingCount(): int
    {
        if (array_key_exists('visible_ratings_count', $this->resource->getAttributes())) {
            return (int) ($this->visible_ratings_count ?? 0);
        }

        if ($this->relationLoaded('ratings')) {
            return (int) $this->ratings->where('is_visible', true)->count();
        }

        return (int) $this->ratings()->where('is_visible', true)->count();
    }
}
