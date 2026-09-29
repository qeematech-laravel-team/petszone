<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Payments\CreateFawryPaymentLinkRequest;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\Order;
use App\Services\Fawry\FawryService;
use App\Services\Geidea\GeideaService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(protected OrderService $service) {}

    /**
     * Display a listing of the authenticated user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $perPage = (int) $request->get('per_page', 15);
        $filters = [
            'search' => (string) $request->get('search', ''),
            'status' => (string) $request->get('status', ''),
            'payment_status' => (string) $request->get('payment_status', ''),
            'payment_method' => (string) $request->get('payment_method', ''),
            'refund_status' => (string) $request->get('refund_status', ''),
            'vendor_id' => $request->get('vendor_id', ''),
            'branch_id' => $request->get('branch_id', ''),
            'from_date' => (string) $request->get('from_date', ''),
            'to_date' => (string) $request->get('to_date', ''),
            'min_total' => $request->get('min_total', ''),
            'max_total' => $request->get('max_total', ''),
            'sort' => (string) $request->get('sort', ''),
        ];

        $orders = $this->service->getPaginatedOrdersForUser($user->id, $perPage, $filters);

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Display the specified order for the authenticated user.
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();

        $order = $this->service->getOrderByIdForUser($id, $user->id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => __('Order not found.'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Calculate shipping costs for the authenticated user's cart.
     *
     * This endpoint calculates shipping costs for all items in the cart without creating an order.
     * It returns detailed shipping information per vendor and total shipping cost.
     */
    public function calculateShipping(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
        ]);

        try {
            $shippingData = $this->service->calculateShippingCost($user->id, $validated['address_id']);

            return response()->json([
                'success' => true,
                'data' => $shippingData,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Create a new order for the authenticated user.
     *
     * This will create the order from the user's cart using the internal order cycle.
     */
    public function store(Request $request, FawryService $fawry, GeideaService $geidea): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            // Inputs that fit the new server-side order cycle
            // Note: order_discount is now calculated automatically from product discounts
            'coupon_code' => ['nullable', 'string', 'exists:coupons,code'],
            // Boolean flags: if true, service will automatically calculate and use all available wallet/points
            'use_wallet' => ['nullable', 'boolean'],
            'use_points' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'payment_method' => ['required', 'string', 'in:COD,FAWRY,GEIDEA'],
            // Optional fields (used when payment_method=FAWRY)
            'return_url' => ['nullable', 'url', 'max:2000'],
            'language' => ['nullable', 'string', 'in:en-gb,ar-eg'], // Geidea language also optional default to 'en-gb'
            'payment_expiry' => ['nullable', 'integer', 'min:0'],
            'webhook_url' => ['nullable', 'url', 'max:2000'],
            'fawry_payment_method' => ['nullable', 'string', 'in:PayAtFawry,CARD,MWALLET,VALU,CashOnDelivery'],
        ]);
        if (! empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', '=', $validated['coupon_code'], 'and')->first();

            if (! $coupon) {
                return response()->json([
                    'success' => false,
                    'message' => __('Coupon not found.'),
                ], 404);
            }

            $validated['coupon_id'] = $coupon->id;
        }

        // Default status to pending if not provided
        if (! isset($validated['status']) || $validated['status'] === '') {
            $validated['status'] = 'pending';
        }

        $order = $this->service->createOrder($user->id, $validated);

        $payment = null;

        if ($validated['payment_method'] === 'FAWRY') {
            $payment = $this->createFawryLink($order, $user, $validated, $fawry);
        }

        if ($validated['payment_method'] === 'GEIDEA') {
            $language = 'EN';
            if (isset($validated['language'])) {
                if ($validated['language'] === 'ar-eg') {
                    $language = 'AR';
                } else {
                    $language = 'EN';
                }
            }
            $payment = $this->createGeideaLink($order, $user, $validated, $geidea, $language);
        }

        return response()->json([
            'success' => true,
            'message' => __('Order created successfully.'),
            'data' => new OrderResource($order),
            'payment' => $payment,
        ], 201);
    }

    /**
     * Cancel an order for the authenticated user (if possible).
     */
    public function cancel(int $id): JsonResponse
    {
        $user = Auth::user();

        $order = $this->service->cancelOrderForUser($id, $user->id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => __('Order not found.'),
            ], 404);
        }

        if ($order->status !== 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => __('Order cannot be cancelled at this stage.'),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => __('Order cancelled successfully.'),
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Reorder items from a previous order by adding them to the user's cart.
     */
    public function reorder(int $id): JsonResponse
    {
        $user = Auth::user();

        try {
            $result = $this->service->reorder($id, $user->id);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => [
                    'added' => $result['added'],
                    'skipped' => $result['skipped'],
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Mark an order as paid immediately (used after successful payment).
     */
    public function pay(int $id, Request $request, FawryService $fawry, GeideaService $geidea): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:COD,FAWRY,GEIDEA'],
            // Optional fields (used when payment_method=FAWRY)
            'return_url' => ['nullable', 'url', 'max:2000'],
            'language' => ['nullable', 'string', 'in:en-gb,ar-eg'], // Geidea language also optional default to 'en-gb'
            'payment_expiry' => ['nullable', 'integer', 'min:0'],
            'webhook_url' => ['nullable', 'url', 'max:2000'],
            'fawry_payment_method' => ['nullable', 'string', 'in:PayAtFawry,CARD,MWALLET,VALU,CashOnDelivery'],
        ]);

        if ($validated['payment_method'] === 'FAWRY') {
            $order = $this->service->getOrderByIdForUser($id, $user->id);

            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => __('Order not found.'),
                ], 404);
            }

            return $this->createFawryLinkResponse($order, $user, $validated, $fawry);
        }

        if ($validated['payment_method'] === 'GEIDEA') {
            $order = $this->service->getOrderByIdForUser($id, $user->id);

            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => __('Order not found.'),
                ], 404);
            }

            $language = 'EN';
            if (isset($validated['language'])) {
                if ($validated['language'] === 'ar-eg') {
                    $language = 'AR';
                } else {
                    $language = 'EN';
                }
            }
            return $this->createGeideaLinkResponse($order, $user, $validated, $geidea, $language);
        }

        $order = $this->service->payOrderImmediatelyForUser($id, $user->id, (string) $validated['payment_method']);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => __('Order not found.'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('Order paid successfully.'),
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Create a FawryPay express checkout payment link for an order.
     */
    public function fawryPaymentLink(int $id, CreateFawryPaymentLinkRequest $request, FawryService $fawry): JsonResponse
    {
        $user = Auth::user();
        $order = $this->service->getOrderByIdForUser($id, $user->id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => __('Order not found.'),
            ], 404);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => __('Order is already paid.'),
            ], 422);
        }

        $validated = $request->validated();

        return $this->createFawryLinkResponse($order, $user, $validated, $fawry);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function createFawryLinkResponse(Order $order, $user, array $validated, FawryService $fawry): JsonResponse
    {
        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => __('Order is already paid.'),
            ], 422);
        }

        $payment = $this->createFawryLink($order, $user, $validated, $fawry);

        if (! $payment['success']) {
            return response()->json([
                'success' => false,
                'message' => $payment['message'],
                'data' => $payment['data'] ?? null,
            ], 502);
        }

        return response()->json([
            'success' => true,
            'data' => $payment['data'],
        ]);
    }

    /**
     * Create a FawryPay express checkout link and return the result as an array.
     *
     * @param  array<string, mixed>  $validated
     * @return array{success: bool, message?: string, data?: array<string, mixed>}
     */
    private function createFawryLink(Order $order, $user, array $validated, FawryService $fawry): array
    {
        // Charge only the remaining amount after wallet balance and loyalty points were applied.
        $payableAmount = $order->payable_amount;

        if ($payableAmount <= 0) {
            return [
                'success' => false,
                'message' => __('This order is fully covered by wallet/points; no payment is required.'),
            ];
        }

        // Fawry docs commonly use numeric merchantRefNum; keep it digits-only to avoid edge cases.
        $merchantRefNum = (string) ($order->id . (int) (microtime(true) * 1000) . random_int(100, 999));
        // Use the app base URL by default (ngrok in dev) unless caller overrides.
        $returnUrl = (string) (Arr::get($validated, 'return_url') ?? rtrim((string) config('app.url'), '/') . '/payment/fawry/return');
        $paymentMethod = (string) (Arr::get($validated, 'fawry_payment_method') ?? 'PayAtFawry');

        $mobile = (string) ($user->phone ?? '');
        // Normalize to the common format expected in many Fawry examples: 01xxxxxxxxx
        if (str_starts_with($mobile, '+20')) {
            $mobile = '0' . ltrim(substr($mobile, 3), '0');
        }

        $data = [
            'merchantRefNum' => $merchantRefNum,
            'customerName' => (string) $user->name,
            'customerMobile' => $mobile,
            'customerEmail' => (string) $user->email,
            // Match Postman: empty customerProfileId
            'customerProfileId' => '',
            // Match Postman: send amount as number (1960.0) while keeping 2-decimal formatting stable.
            'amount' => (float) number_format($payableAmount, 2, '.', ''),
            'currencyCode' => 'EGP',
            // Match Postman: empty string when not used
            'paymentExpiry' => Arr::get($validated, 'payment_expiry') ?? '',
            'language' => (string) (Arr::get($validated, 'language') ?? 'en-gb'),
            'paymentMethod' => $paymentMethod,
            'returnUrl' => $returnUrl,
            'orderWebHookUrl' => Arr::get($validated, 'webhook_url') ?? rtrim((string) config('app.url'), '/') . '/api/payments/fawry/webhook',
            'authCaptureModePayment' => false,
            // Match Postman
            'enable3DS' => true,
            'description' => 'Payment for order-' . $order->id,
        ];

        $items = [
            [
                'itemId' => 'order-' . $order->id,
                'description' => 'Order #' . $order->id,
                'price' => number_format($payableAmount, 2, '.', ''),
                'quantity' => 1,
            ],
        ];

        try {
            // Persist the merchant ref so we can match webhooks back to this order.
            $order->forceFill([
                'payment_method' => 'FAWRY',
                'fawry_merchant_ref_num' => $merchantRefNum,
            ])->save();

            $resp = $fawry->createExpressCheckoutLink($data, $items);
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => __('Failed to create payment link.'),
            ];
        }

        $redirectUrl =
            $resp['redirectUrl']
            ?? ($resp['nextAction']['redirectUrl'] ?? null)
            ?? ($resp['paymentUrl'] ?? null)
            ?? ($resp['url'] ?? null)
            ?? ($resp['paymentLink'] ?? null)
            ?? ($resp['data']['redirectUrl'] ?? null)
            ?? ($resp['data']['paymentUrl'] ?? null)
            ?? ($resp['data']['url'] ?? null);

        $statusCode = (string) ($resp['statusCode'] ?? '');
        $statusDescription = (string) ($resp['statusDescription'] ?? '');

        // If Fawry rejected the request, there will be no redirect URL.
        // Treat this as a failure so clients don't assume a valid payment link exists.
        if ($redirectUrl === null || $redirectUrl === '' || ($statusCode !== '' && $statusCode !== '200')) {
            return [
                'success' => false,
                'message' => __('Failed to create payment link.'),
                'data' => [
                    'merchant_ref_num' => $merchantRefNum,
                    'fawry_status_code' => $statusCode,
                    'fawry_status_description' => $statusDescription,
                    'fawry_response' => $resp,
                ],
            ];
        }

        return [
            'success' => true,
            'data' => [
                'merchant_ref_num' => $merchantRefNum,
                'redirect_url' => $redirectUrl,
                'fawry_response' => $resp,
            ],
        ];
    }

    private function createGeideaLinkResponse(Order $order, $user, array $validated, GeideaService $geidea, string $language): JsonResponse
    {
        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => __('Order is already paid.'),
            ], 422);
        }

        $payment = $this->createGeideaLink($order, $user, $validated, $geidea, $language);

        if (! $payment['success']) {
            return response()->json([
                'success' => false,
                'message' => $payment['message'],
                'data' => $payment['data'] ?? null,
            ], 502);
        }

        return response()->json([
            'success' => true,
            'data' => $payment['data'],
        ]);
    }

    private function createGeideaLink(Order $order, $user, array $validated, GeideaService $geidea, string $language): array
    {
        $payableAmount = (float) $order->payable_amount;
        if ($payableAmount <= 0) {
            return [
                'success' => false,
                'message' => __('This order is fully covered by wallet/points; no payment is required.'),
            ];
        }

        $customer = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ];
        $payable = round((float) $order->payable_amount, 2);
        $invoiceDetails = [
            'eInvoiceItems' => [[
                'eInvoiceItemId' => (string) Str::uuid(),
                'description'    => 'Order #' . $order->id,
                'price'          => $payable,
                'quantity'       => 1,
                'total'          => $payable,
            ]],
            'merchantReferenceId' => 'Order #' . $order->id,
            'subtotal'            => $payable,
            'grand_total'         => $payable,
        ];
        try {
            $resp = $geidea->createPaymentLink($payable, 'EGP', $customer, $invoiceDetails, $language);
        } catch (\RuntimeException $e) {
            return [
                'success' => false,
                'message' => __('Failed to create payment link.'),
                'data'    => ['error' => $e->getMessage()],
            ];
        }

        $intent = $resp['paymentIntent'] ?? [];
        $link = $intent['link'] ?? null;

        if (($resp['responseCode'] ?? null) !== '000' || ! $link) {
            return [
                'success' => false,
                'message' => $resp['detailedResponseMessage'] ?? $resp['responseMessage'] ?? __('Failed to create payment link.'),
                'data'    => [
                    'geidea_response_code' => $resp['responseCode'] ?? null,
                    'geidea_response'      => $resp,
                ],
            ];
        }

        $order->update(['geidea_payment_intent_id' => $intent['paymentIntentId'] ?? null]);

        return [
            'success' => true,
            'data' => [
                'redirect_url'      => $link,
                'payment_intent_id' => $intent['paymentIntentId'] ?? null,
                'status'            => $intent['status'] ?? null,
            ],
        ];
    }
}
