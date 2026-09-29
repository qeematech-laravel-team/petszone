<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FawryWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $payload = (array) $request->all();
        Log::info('Fawry webhook payload', ['payload' => $payload]);

        $secureKey = trim((string) config('services.fawry.hash_key'));
        if ($secureKey === '') {
            Log::error('Fawry webhook: missing secure key config');
            return response()->json(['ok' => false], 500);
        }

        $fawryRefNumber = (string) ($payload['fawryRefNumber'] ?? '');
        $merchantRefNumber = (string) ($payload['merchantRefNumber'] ?? ($payload['merchantRefNum'] ?? ''));
        $paymentAmount = (string) ($payload['paymentAmount'] ?? '');
        $orderAmount = (string) ($payload['orderAmount'] ?? '');
        $orderStatus = (string) ($payload['orderStatus'] ?? '');
        $paymentMethod = (string) ($payload['paymentMethod'] ?? '');
        $paymentRefNumber = (string) ($payload['paymentRefrenceNumber'] ?? ($payload['paymentReferenceNumber'] ?? ''));
        $messageSignature = (string) ($payload['messageSignature'] ?? '');

        $expected = $this->buildMessageSignature(
            fawryRefNumber: $fawryRefNumber,
            merchantRefNumber: $merchantRefNumber,
            paymentAmount: $paymentAmount,
            orderAmount: $orderAmount,
            orderStatus: $orderStatus,
            paymentMethod: $paymentMethod,
            paymentRefNumber: $paymentRefNumber,
            secureKey: $secureKey
        );

        if (! hash_equals($expected, strtolower($messageSignature))) {
            Log::warning('Fawry webhook: signature mismatch', [
                'merchantRefNumber' => $merchantRefNumber,
                'orderStatus' => $orderStatus,
            ]);

            // Return non-200 so Fawry can retry.
            return response()->json(['ok' => false], 400);
        }

        if ($merchantRefNumber === '') {
            Log::warning('Fawry webhook: missing merchantRefNumber', ['payload' => $payload]);
            return response()->json(['ok' => true]);
        }

        $order = Order::query()
            ->where('fawry_merchant_ref_num', '=', $merchantRefNumber)
            ->first();

        if (! $order) {
            Log::warning('Fawry webhook: order not found', [
                'merchantRefNumber' => $merchantRefNumber,
                'fawryRefNumber' => $fawryRefNumber,
            ]);
            // Acknowledge to stop retries; the merchantRefNum might be unknown/old.
            return response()->json(['ok' => true]);
        }

        $order->forceFill([
            'fawry_ref_number' => $fawryRefNumber !== '' ? $fawryRefNumber : $order->fawry_ref_number,
            'fawry_payment_reference_number' => $paymentRefNumber !== '' ? $paymentRefNumber : $order->fawry_payment_reference_number,
        ]);

        // Map Fawry order status to our payment_status.
        switch (strtoupper($orderStatus)) {
            case 'PAID':
                $order->payment_status = 'paid';
                $order->paid_at = now();
                $order->payment_method = 'FAWRY';
                break;
            case 'REFUNDED':
            case 'PARTIAL_REFUNDED':
                $order->payment_status = 'refunded';
                $order->payment_method = 'FAWRY';
                break;
            case 'EXPIRED':
            case 'FAILED':
            case 'CANCELED':
                $order->payment_status = 'failed';
                $order->payment_method = 'FAWRY';
                break;
            default:
                // NEW or unknown statuses: keep current state.
                break;
        }

        $order->save();

        Log::info('Fawry webhook processed', [
            'order_id' => $order->id,
            'merchantRefNumber' => $merchantRefNumber,
            'orderStatus' => $orderStatus,
        ]);

        // Expected response: HTTP 200 + empty body (docs). JSON "ok" is fine too.
        return response()->json(['ok' => true]);
    }

    private function buildMessageSignature(
        string $fawryRefNumber,
        string $merchantRefNumber,
        string $paymentAmount,
        string $orderAmount,
        string $orderStatus,
        string $paymentMethod,
        string $paymentRefNumber,
        string $secureKey
    ): string {
        $pay = number_format((float) $paymentAmount, 2, '.', '');
        $ord = number_format((float) $orderAmount, 2, '.', '');

        $body =
            $fawryRefNumber .
            $merchantRefNumber .
            $pay .
            $ord .
            $orderStatus .
            $paymentMethod .
            $paymentRefNumber .
            $secureKey;

        return hash('sha256', $body);
    }
}

