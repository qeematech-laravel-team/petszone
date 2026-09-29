<?php

namespace App\Services\Geidea;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeideaService
{
    public function createPaymentLink(
        float $amount,
        string $currency,
        array $customer,
        array $invoiceDetails,
        string $language = 'en',
    ): array {

        // Trim to avoid hidden whitespace/newlines from .env breaking signatures.
        $publicKey    = trim((string) config('services.geidea.public_key'));
        $apiPassword    = trim((string) config('services.geidea.api_password'));
        $baseUrl      = rtrim(trim((string) config('services.geidea.base_url')), '/');
        $callbackUrl  = rtrim(trim((string) config('services.geidea.callback_url')), '/');

        if ($publicKey === '' || $apiPassword === '' || $baseUrl === '' || $callbackUrl === '') {
            throw new \RuntimeException('Geidea credentials are not configured.');
        }

        // first three chars in the phone
        $phoneCode = substr($customer['phone'], 0, 3);
        // last 10 chars in the phone
        $phoneNumber = substr($customer['phone'], -10);
        $payload = [
            'amount' => $amount,
            'currency' => $currency,

            'customer' => [
                'name' => $customer['name'],
                'email' => $customer['email'] ?? null,
                'phoneCountryCode' => $phoneCode ?? null,
                'phoneNumber' => $phoneNumber ?? null,
            ],

            'eInvoiceDetails' => [
                'eInvoiceItems' => $invoiceDetails['eInvoiceItems'] ?? [],
                'merchantReferenceId' => $invoiceDetails['merchantReferenceId'] ?? null,
                'subtotal' => $invoiceDetails['subtotal'] ?? $amount,
                'grandTotal' => $invoiceDetails['grand_total'] ?? $amount,
                'language' => $language,
                'callbackUrl' => $callbackUrl,
            ],
        ];

        Log::info('Geidea payment payload', ['payload' => $payload]);

        $url = '/payment-intent/api/v1/direct/eInvoice';
        try {
            $httpResponse = Http::baseUrl($baseUrl)
                ->withBasicAuth($publicKey, $apiPassword)
                ->acceptJson()
                ->asJson()
                ->timeout(30)
                ->post($url, $payload);

            $httpResponse->throw();

            $data = $httpResponse->json();

            if (! is_array($data)) {
                Log::error('Geidea API returned an unexpected non-JSON response', ['body' => $httpResponse->body()]);
                throw new RuntimeException('Geidea API returned an unexpected non-JSON response.');
            }

            Log::info('Geidea payment response', [
                'url'         => $url,
                'http_status' => $httpResponse->status(),
                'response'    => $data,
            ]);

        } catch (ConnectionException $e) {
            Log::error('Geidea payment connection error', [
                'url'     => $url,
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Cannot reach Geidea API.', previous: $e);
        } catch (RequestException $e) {
            $resp = $e->response;
            $body = $resp?->json() ?? $resp?->body();
            Log::error('Geidea payment request error', [
                'url'         => $url,
                'http_status' => $resp?->status(),
                'headers'     => $resp?->headers(),
                'response'    => $body,
            ]);
            throw new \RuntimeException(
                'Geidea API error: ' . (is_string($body) ? $body : json_encode($body)),
                previous: $e
            );
        }

        return $data;
    }
}
