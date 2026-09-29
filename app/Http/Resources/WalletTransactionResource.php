<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
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
            'type' => $this->type,
            'type_label' => $this->type === 'addition' ? __('Addition') : __('Subtraction'),
            'amount' => (float) $this->amount,
            'balance_after' => (float) $this->balance_after,
            'notes' => $this->notes,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_formatted' => $this->created_at->format('M d, Y H:i'),
            'created_at_human' => $this->created_at->diffForHumans(),
        ];
    }
}
