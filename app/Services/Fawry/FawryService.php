<?php

namespace App\Services\Fawry;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FawryService
{
    /**
     * @param  array<int, array{itemId:string, description?:string, price:numeric-string|int|float, quantity:numeric-string|int|float, imageUrl?:string}>  $items
     * @return array<string, mixed>
     */
    public function createExpressCheckoutLink(array $data, array $items): array
    {
        // Trim to avoid hidden whitespace/newlines from .env breaking signatures.
        $merchantCode = trim((string) config('services.fawry.merchant_code'));
        $secureKey    = trim((string) config('services.fawry.hash_key'));
        $baseUrl      = rtrim(trim((string) config('services.fawry.base_url')), '/');
        $apiMode      = (string) config('services.fawry.api_mode', 'charge');
        $initBaseUrl  = rtrim(trim((string) config('services.fawry.init_base_url', $baseUrl)), '/');
        $signatureMode = (string) config('services.fawry.signature_mode', 'express');

        if ($merchantCode === '' || $secureKey === '' || $baseUrl === '') {
            throw new \RuntimeException('Fawry credentials are not configured.');
        }

        $merchantRefNum    = (string) ($data['merchantRefNum'] ?? Str::uuid()->toString());
        $customerProfileId = isset($data['customerProfileId']) ? (string) $data['customerProfileId'] : '';
        $paymentMethod     = (string) ($data['paymentMethod'] ?? '');
        $returnUrl         = (string) ($data['returnUrl'] ?? '');
        if ($returnUrl === '') {
            throw new \InvalidArgumentException('returnUrl is required for Fawry charge request.');
        }

        $amount          = $data['amount'] ?? $this->calculateItemsTotal($items);
        $amountFormatted = $this->formatAmount($amount);

        /**
         * /fawrypay-api/api/payments/init
         * The user asked to match Postman EXACTLY:
         * - payload fields: merchantCode, customerName, customerMobile, customerEmail, customerProfileId (""),
         *   merchantRefNum, amount (number), paymentExpiry (""), currencyCode, language, chargeItems,
         *   paymentMethod, enable3DS, returnUrl, description, signature.
         * - signature string: merchantCode + merchantRefNum + returnUrl + itemId + quantity + price(2dec) + secureKey
         *   (NO customerProfileId in signature).
         */
        if ($apiMode === 'init') {
            $customerProfileId = ''; // force empty string to match Postman

            $initChargeItems = array_map(function (array $item): array {
                return [
                    'itemId' => (string) $item['itemId'],
                    'description' => (string) ($item['description'] ?? ''),
                    'price' => (float) $this->formatAmount($item['price']),
                    'quantity' => (int) round((float) $item['quantity']),
                ];
            }, $items);

            usort($initChargeItems, static fn (array $a, array $b) => strcmp((string) $a['itemId'], (string) $b['itemId']));

            $signature = $this->buildInitPostmanSignature(
                merchantCode: $merchantCode,
                merchantRefNum: $merchantRefNum,
                returnUrl: $returnUrl,
                items: $initChargeItems,
                secureKey: $secureKey
            );

            $payload = [
                'merchantCode' => $merchantCode,
                'customerName' => (string) ($data['customerName'] ?? ''),
                'customerMobile' => (string) ($data['customerMobile'] ?? ''),
                'customerEmail' => (string) ($data['customerEmail'] ?? ''),
                'customerProfileId' => $customerProfileId,
                'merchantRefNum' => $merchantRefNum,
                'amount' => (float) $amountFormatted,
                'paymentExpiry' => (string) ($data['paymentExpiry'] ?? ''),
                'currencyCode' => (string) ($data['currencyCode'] ?? 'EGP'),
                'language' => (string) ($data['language'] ?? 'en-gb'),
                'chargeItems' => $initChargeItems,
                'paymentMethod' => $paymentMethod,
                'enable3DS' => (bool) ($data['enable3DS'] ?? true),
                'returnUrl' => $returnUrl,
                'description' => (string) ($data['description'] ?? ''),
                'signature' => $signature,
            ];

            Log::debug('Fawry init signature components (secureKey excluded)', [
                'merchantCode' => $merchantCode,
                'merchantRefNum' => $merchantRefNum,
                'returnUrl' => $returnUrl,
                'items' => array_map(fn (array $i) => [
                    'itemId' => $i['itemId'],
                    'quantity' => (string) $i['quantity'],
                    'price' => $this->formatAmount($i['price']),
                ], $initChargeItems),
            ]);

            $url = $initBaseUrl . '/fawrypay-api/api/payments/init';

            Log::info('Fawry payment request', [
                'url' => $url,
                'payload' => $payload,
            ]);

            return $this->postToFawry($url, $payload, acceptAny: true);
        }

        // Build two representations:
        // - signatureItems: strings (quantity + price) exactly as required in signature concatenation.
        // - payloadItems: decimals (numbers) as required by the API payload.
        $signatureItems = array_map(function (array $item): array {
            return [
                'itemId'   => (string) $item['itemId'],
                'price'    => $this->formatAmount($item['price']),
                'quantity' => $this->formatQuantity($item['quantity']),
            ];
        }, $items);

        $payloadItems = array_map(function (array $item): array {
            $out = $item;
            $out['itemId'] = (string) $item['itemId'];
            // Send as JSON numbers (Decimal). Trailing zeros are not preserved in JSON, but type is numeric.
            $out['price'] = (float) $this->formatAmount($item['price']);
            $out['quantity'] = (float) $item['quantity'];
            return $out;
        }, $items);

        // Hosted checkout signature requires sorting by itemId.
        usort($signatureItems, static fn (array $a, array $b) => strcmp((string) $a['itemId'], (string) $b['itemId']));
        // Keep payload items sorted as well (nice-to-have, avoids confusion).
        usort($payloadItems, static fn (array $a, array $b) => strcmp((string) $a['itemId'], (string) $b['itemId']));

        /**
         * Docs (Hosted Checkout) signature:
         * merchantCode + merchantRefNum + customerProfileId (or "")
         * + returnUrl
         * + foreach item (sorted by itemId): itemId + quantity + price(2 decimals)
         * + secureKey
         * SHA-256 hashed.
         */
        Log::debug('Fawry charge signature components (secureKey excluded)', [
            'merchantCode'      => $merchantCode,
            'merchantRefNum'    => $merchantRefNum,
            'customerProfileId' => $customerProfileId,
            'returnUrl'         => $returnUrl,
            'signatureMode'     => $signatureMode,
            'items'             => array_map(fn (array $i) => [
                'itemId'   => $i['itemId'],
                'price'    => $i['price'],
                'quantity' => $i['quantity'],
            ], $signatureItems),
            'paymentMethod'     => $paymentMethod,
            'amount'            => $amountFormatted,
        ]);

        $signature = match ($signatureMode) {
            'standard' => $this->buildStandardChargeSignature(
                merchantCode: $merchantCode,
                merchantRefNum: $merchantRefNum,
                customerProfileId: $customerProfileId,
                paymentMethod: $paymentMethod,
                amount: $amountFormatted,
                secureKey: $secureKey
            ),
            default => $this->buildHostedCheckoutSignature(
                merchantCode:      $merchantCode,
                merchantRefNum:    $merchantRefNum,
                customerProfileId: $customerProfileId,
                returnUrl:         $returnUrl,
                items:             $signatureItems,
                secureKey:         $secureKey
            ),
        };

        $payload = array_merge($data, [
            'merchantCode'      => $merchantCode,
            'merchantRefNum'    => $merchantRefNum,
            // Keep as empty string to match Postman/examples (do not omit).
            'customerProfileId' => $customerProfileId,
            // Postman often sends amount as number, but string "xx.xx" is accepted by Fawry.
            'amount'            => $amountFormatted,
            'currencyCode'      => (string) ($data['currencyCode'] ?? 'EGP'),
            'chargeItems'       => $payloadItems,
            'signature'         => $signature,
        ]);

        // Remove nulls — Fawry is fine with missing optional fields.
        // array_filter with === null is safe; no valid field has a null value.
        $payload = array_filter($payload, static fn ($v) => $v !== null);

        // The /fawrypay-api/api/payments/init endpoint appears stricter about JSON types.
        // Match Postman: send amount as a JSON number when using init mode.
        $url = match ($apiMode) {
            'init' => $initBaseUrl . '/fawrypay-api/api/payments/init',
            default => $baseUrl . '/ECommerceWeb/Fawry/payments/charge',
        };

        Log::info('Fawry payment request', [
            'url'     => $url,
            // Never log secureKey. Signature is a one-way hash — still treat as sensitive.
            'payload' => $payload,
        ]);

        try {
            /** @var \Illuminate\Http\Client\Response $httpResponse */
            $httpResponse = Http::asJson()
                ->timeout(30)
                ->withHeaders([
                    'Accept' => $apiMode === 'init' ? '*/*' : 'application/json',
                ])
                ->post($url, $payload);

            $httpResponse->throw();

            $response = $httpResponse->json();

            if ($response === null) {
                Log::error('Fawry returned non-JSON response', ['body' => $httpResponse->body()]);
                throw new \RuntimeException('Fawry returned an unexpected non-JSON response.');
            }

            Log::info('Fawry payment response', [
                'url'         => $url,
                'http_status' => $httpResponse->status(),
                'response'    => $response,
            ]);
        } catch (ConnectionException $e) {
            Log::error('Fawry payment connection error', [
                'url'     => $url,
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Cannot reach Fawry API.', previous: $e);
        } catch (RequestException $e) {
            /** @var \Illuminate\Http\Client\Response|null $resp */
            $resp = $e->response;
            $body = $resp?->json() ?? $resp?->body();
            Log::error('Fawry payment request error', [
                'url'         => $url,
                'http_status' => $resp?->status(),
                'headers'     => $resp?->headers(),
                'response'    => $body,
            ]);
            throw new \RuntimeException(
                'Fawry API error: ' . (is_string($body) ? $body : json_encode($body)),
                previous: $e
            );
        }

        return is_array($response) ? $response : ['raw' => $response];
    }

    /**
     * Postman-matching init signature:
     * merchantCode + merchantRefNum + returnUrl + itemId + quantity + price(2 decimals) + secureKey
     *
     * @param array<int, array{itemId:string, price:float, quantity:int}> $items
     */
    private function buildInitPostmanSignature(
        string $merchantCode,
        string $merchantRefNum,
        string $returnUrl,
        array $items,
        string $secureKey
    ): string {
        $body = $merchantCode . $merchantRefNum . $returnUrl;

        foreach ($items as $item) {
            $body .= (string) $item['itemId'];
            $body .= (string) $item['quantity'];
            $body .= $this->formatAmount($item['price']);
        }

        $body .= $secureKey;

        return hash('sha256', $body);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function postToFawry(string $url, array $payload, bool $acceptAny = false): array
    {
        try {
            /** @var \Illuminate\Http\Client\Response $httpResponse */
            $httpResponse = Http::asJson()
                ->timeout(30)
                ->withHeaders([
                    'Accept' => $acceptAny ? '*/*' : 'application/json',
                ])
                ->post($url, $payload);

            $httpResponse->throw();

            $response = $httpResponse->json();

            if ($response === null) {
                $body = trim((string) $httpResponse->body());

                // Some endpoints (notably /fawrypay-api/api/payments/init) may return a plain text URL.
                if ($body !== '' && (str_starts_with($body, 'http://') || str_starts_with($body, 'https://'))) {
                    Log::info('Fawry returned payment URL (non-JSON)', [
                        'url' => $url,
                        'http_status' => $httpResponse->status(),
                        'payment_url' => $body,
                    ]);

                    return [
                        'paymentUrl' => $body,
                        'redirectUrl' => $body,
                        'rawBody' => $body,
                    ];
                }

                Log::error('Fawry returned non-JSON response', ['body' => $body]);
                throw new \RuntimeException('Fawry returned an unexpected non-JSON response.');
            }

            Log::info('Fawry payment response', [
                'url' => $url,
                'http_status' => $httpResponse->status(),
                'response' => $response,
            ]);

            return is_array($response) ? $response : ['raw' => $response];
        } catch (ConnectionException $e) {
            Log::error('Fawry payment connection error', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Cannot reach Fawry API.', previous: $e);
        } catch (RequestException $e) {
            /** @var \Illuminate\Http\Client\Response|null $resp */
            $resp = $e->response;
            $body = $resp?->json() ?? $resp?->body();
            Log::error('Fawry payment request error', [
                'url' => $url,
                'http_status' => $resp?->status(),
                'headers' => $resp?->headers(),
                'response' => $body,
            ]);
            throw new \RuntimeException(
                'Fawry API error: ' . (is_string($body) ? $body : json_encode($body)),
                previous: $e
            );
        }
    }

    /**
     * Hosted Checkout (Express Checkout Link) signature (per docs):
     * merchantCode + merchantRefNum + customerProfileId (or "" if absent) + returnUrl
     * + per item (sorted by itemId): itemId + quantity + price (2 decimals)
     * + secureKey
     * SHA-256 hashed.
     *
     * @param array<int, array{itemId:string, price:string, quantity:string}> $items
     */
    private function buildHostedCheckoutSignature(
        string $merchantCode,
        string $merchantRefNum,
        string $customerProfileId,
        string $returnUrl,
        array  $items,
        string $secureKey
    ): string {
        $body = $merchantCode . $merchantRefNum . $customerProfileId . $returnUrl;

        foreach ($items as $item) {
            $body .= (string) $item['itemId'];
            $body .= $item['price'];      // already formatted by normalizedItems
            $body .= $item['quantity'];   // already formatted by normalizedItems
        }

        $body .= $secureKey;

        return hash('sha256', $body);
    }

    /**
     * Standard charge signature (other docs / some merchant profiles):
     * merchantCode + merchantRefNum + customerProfileId(or "") + paymentMethod + amount(2 decimals) + secureKey
     */
    private function buildStandardChargeSignature(
        string $merchantCode,
        string $merchantRefNum,
        string $customerProfileId,
        string $paymentMethod,
        string $amount,
        string $secureKey
    ): string {
        // Standard charge signature (as used in some Fawry charge APIs):
        // SHA-256(merchantCode + merchantRefNum + customerProfileId(if exists else "")
        // + paymentMethod + amount(2 decimals) + secureKey)
        return hash('sha256', $merchantCode . $merchantRefNum . $customerProfileId . $paymentMethod . $amount . $secureKey);
    }

    private function formatAmount(string|int|float $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    /**
     * @param  array<int, array{price:numeric-string|int|float, quantity:numeric-string|int|float}>  $items
     */
    private function calculateItemsTotal(array $items): float
    {
        $total = 0.0;

        foreach ($items as $item) {
            $total += (float) $item['price'] * (float) $item['quantity'];
        }

        return $total;
    }

    private function formatQuantity(string|int|float $qty): string
    {
        return number_format((float) $qty, 2, '.', ''); // "1.00"
    }
}
