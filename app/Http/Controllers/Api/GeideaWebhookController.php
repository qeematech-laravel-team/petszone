<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeideaWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $payload = (array) $request->all();
        Log::info('Geidea webhook payload', ['payload' => $payload]);

        $publicKey = trim((string) config('services.geidea.public_key'));
        $apiPassword = trim((string) config('services.geidea.api_password'));

        if ($publicKey === '' || $apiPassword === '') {
            Log::error('Geidea webhook: missing credentials config');
            return response()->json(['ok' => false], 500);
        }

        $data = (array) ($payload['order'] ?? []);
        $signature = (string) ($payload['signature'] ?? '');
        $timeStamp = (string) ($payload['timeStamp'] ?? '');

        $geideaOrderId = (string) ($data['orderId'] ?? '');
        $amount = (float) ($data['amount'] ?? 0);
        $currency = (string) ($data['currency'] ?? '');
        $status = (string) ($data['status'] ?? '');
        $detailedStatus = (string) ($data['detailedStatus'] ?? '');
        $merchantReferenceId = (string) ($data['merchantReferenceId'] ?? '');
        $paymentIntentId = (string) ($data['paymentIntentId'] ?? ($data['paymentIntent']['paymentIntentId'] ?? ''));

        $expected = base64_encode(hash_hmac(
            'sha256',
            $publicKey . number_format($amount, 2, '.', '') . $currency . $geideaOrderId . $status . $merchantReferenceId . $timeStamp,
            $apiPassword,
            true
        ));

        if ($signature === '' || ! hash_equals($expected, $signature)) {
            Log::warning('Geidea webhook: signature mismatch', [
                'geidea_order_id' => $geideaOrderId,
                'merchantReferenceId' => $merchantReferenceId,
            ]);
            return response()->json(['ok' => false], 400);
        }

        $order = $this->findOrder($paymentIntentId, $merchantReferenceId);

        if (! $order) {
            Log::warning('Geidea webhook: order not found', [
                'paymentIntentId' => $paymentIntentId,
                'merchantReferenceId' => $merchantReferenceId,
            ]);
            return response()->json(['ok' => true]);
        }

        DB::transaction(function () use ($order, $status, $detailedStatus, $amount, $currency, $geideaOrderId, $paymentIntentId) {
            $order = Order::query()->lockForUpdate()->find($order->id);

            if ($order->payment_status === 'paid' && strcasecmp($detailedStatus, 'Refunded') !== 0) {
                return;
            }

            $order->forceFill([
                'geidea_order_id' => $geideaOrderId ?: $order->geidea_order_id,
                'geidea_payment_intent_id' => $paymentIntentId ?: $order->geidea_payment_intent_id,
                'payment_method' => 'GEIDEA',
            ]);

            $isSuccess = strcasecmp($status, 'Success') === 0;

            if ($isSuccess && in_array(strtolower($detailedStatus), ['paid', 'captured', 'authorized'], true)) {
                if (abs($amount - (float) $order->payable_amount) > 0.01 || strtoupper($currency) !== 'EGP') {
                    Log::error('Geidea webhook: amount/currency mismatch', [
                        'order_id' => $order->id,
                        'paid' => $amount,
                        'currency' => $currency,
                        'expected' => $order->payable_amount,
                    ]);
                    $order->save();
                    return;
                }

                $order->payment_status = 'paid';
                $order->paid_at = now();
            } elseif (in_array(strtolower($detailedStatus), ['refunded', 'partiallyrefunded'], true)) {
                $order->payment_status = 'refunded';
            } elseif (! $isSuccess || in_array(strtolower($detailedStatus), ['failed', 'cancelled', 'expired', 'authenticationfailed'], true)) {
                if ($order->payment_status !== 'paid') {
                    $order->payment_status = 'failed';
                }
            }

            $order->save();

            Log::info('Geidea webhook processed', [
                'order_id' => $order->id,
                'status' => $status,
                'detailedStatus' => $detailedStatus,
                'payment_status' => $order->payment_status,
            ]);
        });

        return response()->json(['ok' => true]);
    }

    private function findOrder(string $paymentIntentId, string $merchantReferenceId): ?Order
    {
        if ($paymentIntentId !== '') {
            $order = Order::query()->where('geidea_payment_intent_id', $paymentIntentId)->first();
            if ($order) {
                return $order;
            }
        }

        if (preg_match('/(\d+)\s*$/', $merchantReferenceId, $m)) {
            return Order::query()->find((int) $m[1]);
        }

        return null;
    }
}
