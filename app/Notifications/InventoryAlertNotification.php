<?php

namespace App\Notifications;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InventoryAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $branchId,
        public int $vendorId,
        public ?Product $product,
        public ?ProductVariant $variant,
        public int $quantity,
        public int $threshold,
        public string $level // low | out
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subjectLine())
            ->line($this->messageLine())
            ->line(__('Branch ID: :branch_id', ['branch_id' => $this->branchId]))
            ->line(__('Current quantity: :qty', ['qty' => $this->quantity]))
            ->line(__('Threshold: :threshold', ['threshold' => $this->threshold]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'inventory_alert',
            'level' => $this->level,
            'vendor_id' => $this->vendorId,
            'branch_id' => $this->branchId,
            'product_id' => $this->product?->id,
            'product_variant_id' => $this->variant?->id,
            'quantity' => $this->quantity,
            'threshold' => $this->threshold,
            'title' => $this->subjectLine(),
            'message' => $this->messageLine(),
        ];
    }

    protected function subjectLine(): string
    {
        return $this->level === 'out'
            ? __('Out of stock alert')
            : __('Low stock alert');
    }

    protected function messageLine(): string
    {
        $name = $this->productName();

        if ($this->variant) {
            return $this->level === 'out'
                ? __('Variant is out of stock: :name (Variant #:id)', ['name' => $name, 'id' => $this->variant->id])
                : __('Variant low stock: :name (Variant #:id)', ['name' => $name, 'id' => $this->variant->id]);
        }

        return $this->level === 'out'
            ? __('Product is out of stock: :name (Product #:id)', ['name' => $name, 'id' => $this->product?->id])
            : __('Product low stock: :name (Product #:id)', ['name' => $name, 'id' => $this->product?->id]);
    }

    protected function productName(): string
    {
        if (! $this->product) {
            return '-';
        }

        $name = $this->product->name;

        if (is_array($name)) {
            return (string) ($name['en'] ?? $name['ar'] ?? reset($name) ?? '-');
        }

        return (string) ($name ?? '-');
    }
}
