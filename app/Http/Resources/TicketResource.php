<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
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
            'vendor_id' => $this->vendor_id,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'ticket_from' => $this->ticket_from,
            'attachments' => $this->when($this->attachments, function () {
                return collect($this->attachments)->map(function ($attachment) {
                    return asset('storage/'.$attachment);
                })->toArray();
            }),
            'user' => new UserResource($this->whenLoaded('user')),
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'messages' => TicketMessageResource::collection($this->whenLoaded('messages')),
            'messages_count' => $this->when($this->relationLoaded('messages'), function () {
                return $this->messages->count();
            }, function () {
                return $this->messages()->count();
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
