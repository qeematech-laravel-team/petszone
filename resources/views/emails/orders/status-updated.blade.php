<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Order Status Update') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
            color: #111827;
        }

        .container {
            max-width: 640px;
            margin: 32px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #1e5a8a, #2c7eb8);
            color: #ffffff;
            padding: 20px 24px;
            text-align: left;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .body {
            padding: 24px 24px 20px;
            font-size: 14px;
            line-height: 1.6;
        }

        .pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            background-color: #e5f3ff;
            color: #1d4ed8;
            margin-top: 4px;
        }

        .order-meta {
            margin: 18px 0;
            padding: 12px 14px;
            background-color: #f9fafb;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }

        .order-meta div {
            margin-bottom: 4px;
        }

        .order-meta div:last-child {
            margin-bottom: 0;
        }

        .footer {
            padding: 16px 24px 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ __('Your order status has changed') }}</h1>
    </div>

    <div class="body">
        <p>{{ __('Hello') }} {{ $order->user->name ?? '' }},</p>

        @php
            $statusLabel = ucfirst($status);
        @endphp

        @if($vendorOrder)
            <p>
                {{ __('The status of part of your order has been updated by the vendor.') }}
            </p>
            <div class="order-meta">
                <div><strong>{{ __('Order ID') }}:</strong> #{{ $order->id }}</div>
                <div><strong>{{ __('Vendor Order ID') }}:</strong> #{{ $vendorOrder->id }}</div>
                <div><strong>{{ __('Vendor') }}:</strong> {{ $vendorOrder->vendor->name ?? __('Unknown vendor') }}</div>
                <div>
                    <strong>{{ __('New Status') }}:</strong>
                    <span class="pill">{{ $statusLabel }}</span>
                </div>
            </div>
        @else
            <p>
                {{ __('The status of your order has been updated.') }}
            </p>
            <div class="order-meta">
                <div><strong>{{ __('Order ID') }}:</strong> #{{ $order->id }}</div>
                <div>
                    <strong>{{ __('New Status') }}:</strong>
                    <span class="pill">{{ $statusLabel }}</span>
                </div>
                <div>
                    <strong>{{ __('Total') }}:</strong>
                    {{ number_format($order->total, 2) }} {{ setting('currency', 'EGP') }}
                </div>
            </div>
        @endif

        <p>
            {{ __('You can review the latest details of your order from your account.') }}
        </p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
    </div>
</div>
</body>
</html>

