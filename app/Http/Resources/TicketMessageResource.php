<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketMessageResource extends JsonResource
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
            'ticket_id' => $this->ticket_id,
            'sender_type' => $this->sender_type,
            'sender' => new UserResource($this->whenLoaded('sender')),
            'message' => $this->message,
            'attachments' => $this->when($this->attachments, function () {
                return collect($this->attachments)->map(function ($attachment) {
                    return asset('storage/'.$attachment);
                })->toArray();
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
