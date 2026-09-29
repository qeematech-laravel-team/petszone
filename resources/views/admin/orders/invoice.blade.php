<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Invoice') }} - #{{ $order->id }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #1f2937;
            background: #ffffff;
        }

        .page {
            max-width: 920px;
            margin: 0 auto;
            padding: 32px 28px 40px;
        }

        .no-print {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            background: #fff;
            color: #1f2937;
            text-decoration: none;
            font-size: 14px;
            line-height: 1;
        }

        .btn:hover {
            background: #f8fafc;
        }

        .card {
            border: 1px solid #e5e7eb;
            padding: 22px;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        .logo {
            height: 56px;
            object-fit: contain;
            margin-bottom: 8px;
        }

        .title {
            margin: 6px 0 0;
            font-size: 22px;
            font-weight: 700;
        }

        .info {
            margin-bottom: 16px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 0;
        }

        .info-table td {
            padding: 6px 0;
            border: none;
            vertical-align: middle;
        }

        .info-table td strong {
            color: #1f2937;
        }

        .info-table td:last-child {
            text-align: right;
        }

        .muted {
            color: #6b7280;
        }

        .bill-ship {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            margin: 18px 0;
            font-size: 14px;
        }

        .label-box {
            background: #e8edff;
            padding: 10px 15px;
            font-weight: 700;
            color: #1f2937;
            font-size: 13px;
            margin-bottom: 8px;
            width: fit-content;
        }

        .address-block {
            padding: 10px 0 0;
            line-height: 1.6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        thead th {
            background: #e8edff;
            font-weight: 700;
            text-align: left;
            font-size: 13px;
            padding: 10px;
            border: 1px solid #e5e7eb;
        }

        tbody td {
            font-size: 13px;
            padding: 10px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals {
            width: 100%;
            margin-top: 18px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .totals-row {
            display: grid;
            grid-template-columns: 1fr 0.2fr 1fr 1fr 1fr;
            padding: 10px 12px;
            font-size: 13px;
            align-items: center;
        }

        .totals-row:nth-child(odd) {
            background: #e8edff;
        }

        .totals-row:nth-child(even) {
            background: #f8fafc;
        }

        .totals-row strong {
            font-weight: 700;
        }

        .danger {
            color: #dc2626;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .page {
                padding: 0 0 12px;
                max-width: none;
            }

            .card {
                border: none;
                padding: 0;
            }

            a[href]:after {
                content: "";
            }
        }

        @media (max-width: 640px) {
            .info-table td:last-child {
                text-align: left;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        @if (empty($asPdf))
            <div class="no-print">
                <div class="muted">{{ __('Print to PDF from your browser (Ctrl+P).') }}</div>
                <a class="btn" href="{{ route('admin.orders.show', $order->id) }}">{{ __('Back') }}</a>
            </div>
        @endif

        <div class="card">
            <div class="header">
                @php
                    $logo = setting('app_logo');
                @endphp
                @if ($logo)
                    <img src="{{ asset('storage/' . $logo) }}" alt="{{ config('app.name') }}" class="logo">
                @endif
                <h1 class="title">{{ __('Invoice') }}</h1>
            </div>

            <div class="info">
                <table class="info-table">
                    <tr>
                        <td>
                            <strong>{{ __('Invoice ID') }}:</strong>
                            <span class="muted">#{{ $order->id }}</span>
                        </td>
                        <td>
                            <strong>{{ __('Order Date') }}:</strong>
                            <span class="muted">{{ optional($order->created_at)->format('d-m-Y') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>{{ __('Customer Name') }}:</strong>
                            <span class="muted">{{ $order->user->name ?? '-' }}</span>
                        </td>
                        <td>
                            <strong>{{ __('Customer Phone') }}:</strong>
                            <span class="muted">{{ $order->address->phone ?? ($order->user->phone ?? '-') }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="bill-ship">
                <div>
                    <div class="label-box">{{ __('Bill & Ship To') }}</div>
                    <div class="address-block">
                        {{ $order->user->name ?? '-' }}<br>
                        @if ($order->address)
                            {{ $order->address->address_line_1 }}<br>
                            @if ($order->address->address_line_2)
                                {{ $order->address->address_line_2 }}<br>
                            @endif
                            {{ $order->address->city ?? '' }}@if ($order->address->state)
                                , {{ $order->address->state }}
                            @endif
                            <br>
                            {{ $order->address->postal_code ?? '' }}<br>
                            {{ $order->address->phone ?? '' }}
                        @else
                            <span class="muted">-</span>
                        @endif
                    </div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Product Name') }}</th>
                        <th class="text-end">{{ __('Price') }}</th>
                        <th class="text-center">{{ __('Qty') }}</th>
                        <th class="text-end">{{ __('Subtotal') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product->sku ?? '-' }}</td>
                            <td>
                                {{ $item->product->name ?? '-' }}
                                @if ($item->variant)
                                    <div class="muted" style="margin-top:4px;">{{ $item->variant->name }}</div>
                                @endif
                            </td>
                            <td class="text-end">{{ number_format($item->price, 2) }} {{ setting('currency', 'EGP') }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end"><strong>{{ number_format($item->total, 2) }}
                                    {{ setting('currency', 'EGP') }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($order->vendorOrders && $order->vendorOrders->count())
                <h3 style="margin-top:24px; font-size:14px; font-weight:700;">{{ __('Vendors') }}</h3>
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Vendor') }}</th>
                            <th class="text-end">{{ __('Subtotal') }}</th>
                            <th class="text-end">{{ __('Shipping') }}</th>
                            <th class="text-end">{{ __('Total') }}</th>
                            <th class="text-center">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->vendorOrders as $vendorOrder)
                            <tr>
                                <td>{{ $vendorOrder->vendor->name ?? __('Unknown Vendor') }}</td>
                                <td class="text-end">{{ number_format($vendorOrder->sub_total, 2) }} {{ setting('currency', 'EGP') }}</td>
                                <td class="text-end">{{ number_format($vendorOrder->shipping_cost, 2) }} {{ setting('currency', 'EGP') }}</td>
                                <td class="text-end"><strong>{{ number_format($vendorOrder->total, 2) }} {{ setting('currency', 'EGP') }}</strong></td>
                                <td class="text-center">{{ ucfirst($vendorOrder->status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @php
                $discountTotal = (float) $order->order_discount + (float) $order->coupon_discount;
            @endphp

            <div class="totals">
                <div class="totals-row">
                    <span class="muted">{{ __('Subtotal') }}</span><span>:</span><span></span><span></span>
                    <strong>{{ number_format($order->sub_total, 2) }} {{ setting('currency', 'EGP') }}</strong>
                </div>
                <div class="totals-row">
                    <span class="muted">{{ __('Discount') }}</span><span>:</span><span></span><span></span>
                    <strong class="danger">{{ number_format($discountTotal, 2) }}
                        {{ setting('currency', 'EGP') }}</strong>
                </div>
                <div class="totals-row">
                    <span class="muted">{{ __('Shipping') }}</span><span>:</span><span></span><span></span>
                    <strong>{{ number_format($order->total_shipping ?? 0, 2) }}
                        {{ setting('currency', 'EGP') }}</strong>
                </div>
                <div class="totals-row">
                    <strong>{{ __('Grand Total') }}</strong><span>:</span><span></span><span></span>
                    <strong>{{ number_format($order->total, 2) }} {{ setting('currency', 'EGP') }}</strong>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

