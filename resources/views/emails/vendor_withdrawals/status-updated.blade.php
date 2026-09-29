<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Withdrawal Status Update') }}</title>
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
            @if($status === 'approved')
                background-color: #d1fae5;
                color: #065f46;
            @elseif($status === 'rejected')
                background-color: #fee2e2;
                color: #991b1b;
            @else
                background-color: #e5f3ff;
                color: #1d4ed8;
            @endif
            margin-top: 4px;
        }

        .withdrawal-meta {
            margin: 18px 0;
            padding: 12px 14px;
            background-color: #f9fafb;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }

        .withdrawal-meta div {
            margin-bottom: 8px;
        }

        .withdrawal-meta div:last-child {
            margin-bottom: 0;
        }

        .amount {
            font-size: 20px;
            font-weight: 600;
            color: #059669;
        }

        .notes-section {
            margin-top: 16px;
            padding: 12px;
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 6px;
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
        <h1>
            @if($status === 'approved')
                {{ __('Withdrawal Request Approved') }}
            @elseif($status === 'rejected')
                {{ __('Withdrawal Request Rejected') }}
            @else
                {{ __('Withdrawal Status Update') }}
            @endif
        </h1>
    </div>

    <div class="body">
        <p>{{ __('Hello') }} {{ $withdrawal->vendor->owner->name ?? __('Vendor') }},</p>

        @if($status === 'approved')
            <p>
                {{ __('We are pleased to inform you that your withdrawal request has been approved and processed.') }}
            </p>
        @elseif($status === 'rejected')
            <p>
                {{ __('We regret to inform you that your withdrawal request has been rejected.') }}
            </p>
        @else
            <p>
                {{ __('Your withdrawal request status has been updated.') }}
            </p>
        @endif

        <div class="withdrawal-meta">
            <div><strong>{{ __('Withdrawal ID') }}:</strong> #{{ $withdrawal->id }}</div>
            <div>
                <strong>{{ __('Amount') }}:</strong>
                <span class="amount">{{ number_format($withdrawal->amount, 2) }} {{ setting('currency', 'EGP') }}</span>
            </div>
            @if($withdrawal->method)
                <div><strong>{{ __('Method') }}:</strong> {{ $withdrawal->method }}</div>
            @endif
            <div>
                <strong>{{ __('Status') }}:</strong>
                <span class="pill">{{ ucfirst($status) }}</span>
            </div>
            @if($withdrawal->processed_at)
                <div><strong>{{ __('Processed At') }}:</strong> {{ $withdrawal->processed_at->format('Y-m-d H:i') }}</div>
            @endif
            @if($withdrawal->balance_after !== null)
                <div><strong>{{ __('Current Balance') }}:</strong> {{ number_format($withdrawal->balance_after, 2) }} {{ setting('currency', 'EGP') }}</div>
            @endif
        </div>

        @if($status === 'rejected' && $notes)
            <div class="notes-section">
                <strong>{{ __('Rejection Reason') }}:</strong>
                <p style="margin: 8px 0 0 0;">{{ $notes }}</p>
            </div>
        @endif

        @if($status === 'approved')
            <p>
                {{ __('The amount has been deducted from your account balance and will be transferred according to your specified payment method.') }}
            </p>
        @endif

        <p>
            {{ __('You can review the details of this withdrawal request from your vendor dashboard.') }}
        </p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
    </div>
</div>
</body>
</html>
