<?php

namespace App\Services;

use App\Jobs\SendFirebaseNotification;
use App\Models\Address;
use App\Models\Branch;
use App\Models\BranchProductStock;
use App\Models\BranchProductVariantStock;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBalanceTransaction;
use App\Models\VendorOrder;
use App\Models\VendorOrderItem;
use App\Models\VendorSetting;
use App\Models\WalletTransaction;
use App\Notifications\NewOrderForAdminNotification;
use App\Notifications\NewVendorOrderNotification;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusUpdatedNotification;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /** @var array<int, Collection>|null */
    private ?array $activeBranchesByVendorId = null;

    /** @var array<int, Collection>|null */
    private ?array $vendorSettingsByVendorId = null;

    /** @var array<int, Vendor>|null */
    private ?array $vendorsById = null;

    /** @var array<string, BranchProductStock>|null */
    private ?array $productStocksByKey = null;

    /** @var array<string, BranchProductVariantStock>|null */
    private ?array $variantStocksByKey = null;

    public function __construct(
        protected OrderRepository $orderRepository,
        protected CartRepository $cartRepository,
        protected CartService $cartService,
        protected NotificationService $notificationService,
        protected InventoryAlertService $inventoryAlertService
    ) {}

    /**
     * Get paginated orders.
     *
     * @param  array{
     *   search?: string,
     *   status?: string,
     *   user_id?: int|string,
     *   vendor_id?: int|string,
     *   branch_id?: int|string,
     *   coupon_id?: int|string,
     *   address_id?: int|string,
     *   vendor_order_status?: string,
     *   from_date?: string,
     *   to_date?: string,
     *   min_total?: float|int|string,
     *   max_total?: float|int|string,
     * }  $filters
     */
    public function getPaginatedOrders(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->orderRepository->getPaginatedOrders($perPage, $filters);
    }

    /**
     * Get order by id.
     */
    public function getOrderById(int $id): ?Order
    {
        return $this->orderRepository->getOrderById($id);
    }

    public function payOrderImmediatelyForUser(int $orderId, int $userId, string $paymentMethod): ?Order
    {
        $order = $this->orderRepository->getOrderByIdForUser($orderId, $userId);

        if (! $order) {
            return null;
        }

        return $this->payOrderImmediately($order, $paymentMethod, $userId);
    }

    public function payOrderImmediately(Order $order, string $paymentMethod, ?int $actorUserId = null): Order
    {
        return DB::transaction(function () use ($order, $paymentMethod, $actorUserId) {
            /** @var Order $order */
            $order = Order::query()
                ->with(['vendorOrders'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($order->payment_status === 'paid') {
                return $order;
            }

            $fromStatus = (string) $order->payment_status;

            $order->payment_status = 'paid';
            $order->payment_method = $paymentMethod;
            $order->paid_at = now();
            $order->save();

            if ($order->vendor_balance_processed_at) {
                return $order;
            }

            $profitType = (string) setting('profit_type');

            foreach ($order->vendorOrders as $vendorOrder) {
                /** @var Vendor $vendor */
                $vendor = Vendor::query()->lockForUpdate()->findOrFail((int) $vendorOrder->vendor_id);

                $gross = (float) $vendorOrder->total;
                $commission = (float) $vendorOrder->commission;
                $net = $profitType === 'commission' ? max(0, $gross - $commission) : $gross;

                $vendor->balance = round((float) $vendor->balance + $net, 2);
                $vendor->save();

                VendorBalanceTransaction::create([
                    'vendor_id' => $vendor->id,
                    'order_id' => $order->id,
                    'vendor_order_id' => $vendorOrder->id,
                    'type' => 'addition',
                    'amount' => round($net, 2),
                    'balance_after' => $vendor->balance,
                    'notes' => 'Order #'.$order->id.' (Vendor Order #'.$vendorOrder->id.')',
                    'payload' => [
                        'profit_type' => $profitType,
                        'gross' => round($gross, 2),
                        'commission' => round($commission, 2),
                        'payment_method' => $paymentMethod,
                    ],
                ]);
            }

            $order->vendor_balance_processed_at = now();
            $order->save();

            OrderLog::create([
                'order_id' => $order->id,
                'vendor_order_id' => null,
                'user_id' => $actorUserId,
                'type' => 'payment_change',
                'from_status' => $fromStatus,
                'to_status' => 'paid',
                'payload' => [
                    'payment_method' => $paymentMethod,
                ],
            ]);

            return $order->refresh();
        });
    }

    /**
     * Get orders by user id.
     */
    public function getOrdersByUser(int $userId): Collection
    {
        return $this->orderRepository->getOrdersByUser($userId);
    }

    /**
     * Get paginated orders for a specific user (API user orders list).
     *
     * @param  array{
     *   search?: string,
     *   status?: string,
     *   vendor_id?: int|string,
     *   branch_id?: int|string,
     *   from_date?: string,
     *   to_date?: string,
     *   min_total?: float|int|string,
     *   max_total?: float|int|string,
     * }  $filters
     */
    public function getPaginatedOrdersForUser(int $userId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->orderRepository->getPaginatedOrdersForUser($userId, $perPage, $filters);
    }

    /**
     * Get order details for a specific user (API user order show).
     */
    public function getOrderByIdForUser(int $id, int $userId): ?Order
    {
        return $this->orderRepository->getOrderByIdForUser($id, $userId);
    }

    /**
     * Get paginated orders for a specific vendor (vendor dashboard orders list).
     *
     * @param  array{
     *   search?: string,
     *   status?: string,
     *   vendor_order_status?: string,
     *   branch_id?: int|string,
     *   from_date?: string,
     *   to_date?: string,
     *   min_total?: float|int|string,
     *   max_total?: float|int|string,
     * }  $filters
     */
    public function getPaginatedOrdersForVendor(int $vendorId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->orderRepository->getPaginatedOrdersForVendor($vendorId, $perPage, $filters);
    }

    /**
     * Get order details for a specific vendor (vendor dashboard order show).
     */
    public function getOrderByIdForVendor(int $id, int $vendorId): ?Order
    {
        return $this->orderRepository->getOrderByIdForVendor($id, $vendorId);
    }

    /**
     * Get paginated vendor orders for a specific vendor (vendor_orders list).
     *
     * @param  array{
     *   search?: string,
     *   status?: string,
     *   branch_id?: int|string,
     *   order_id?: int|string,
     *   from_date?: string,
     *   to_date?: string,
     * }  $filters
     */
    public function getPaginatedVendorOrdersForVendor(int $vendorId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->orderRepository->getPaginatedVendorOrdersForVendor($vendorId, $perPage, $filters);
    }

    /**
     * Get vendor order details for a specific vendor (vendor_orders show).
     */
    public function getVendorOrderByIdForVendor(int $id, int $vendorId): ?VendorOrder
    {
        return $this->orderRepository->getVendorOrderByIdForVendor($id, $vendorId);
    }

    /**
     * Update vendor order.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateVendorOrder(VendorOrder $vendorOrder, array $data): bool
    {
        return DB::transaction(function () use ($vendorOrder, $data) {
            return $vendorOrder->update($data);
        });
    }

    /**
     * Update vendor order status and sync main order when all vendor orders are delivered.
     */
    public function updateVendorOrderStatus(VendorOrder $vendorOrder, string $status): bool
    {
        $currentStatus = $vendorOrder->status;

        if ($status === $currentStatus) {
            return true;
        }

        $allowedTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'cancelled'],
            'delivered' => [],
            'cancelled' => [],
        ];

        $canTransition = $allowedTransitions[$currentStatus] ?? [];

        if (! in_array($status, $canTransition, true)) {
            return false;
        }

        return DB::transaction(function () use ($vendorOrder, $status, $currentStatus) {
            $updated = $vendorOrder->update(['status' => $status]);

            if (! $updated) {
                return false;
            }

            $order = $vendorOrder->order()->lockForUpdate()->first();

            if ($order && $order->status !== 'cancelled') {
                $previousOrderStatus = $order->status;

                if ($status === 'processing' && $order->status === 'pending') {
                    // As soon as any vendor order moves to processing, move main order to processing
                    $this->orderRepository->update($order, ['status' => 'processing']);

                    OrderLog::create([
                        'order_id' => $order->id,
                        'vendor_order_id' => $vendorOrder->id,
                        'user_id' => Auth::id(),
                        'type' => 'order_status_change',
                        'from_status' => $previousOrderStatus,
                        'to_status' => 'processing',
                        'payload' => null,
                    ]);
                }

                if ($status === 'delivered') {
                    $hasUndeliveredSiblings = VendorOrder::query()
                        ->where('order_id', $order->id)
                        ->where('id', '!=', $vendorOrder->id)
                        ->where('status', '!=', 'delivered')
                        ->exists();

                    if (! $hasUndeliveredSiblings) {
                        $this->orderRepository->update($order, ['status' => 'delivered']);

                        OrderLog::create([
                            'order_id' => $order->id,
                            'vendor_order_id' => $vendorOrder->id,
                            'user_id' => Auth::id(),
                            'type' => 'order_status_change',
                            'from_status' => $previousOrderStatus,
                            'to_status' => 'delivered',
                            'payload' => null,
                        ]);
                    }
                }

                // Notify user about vendor order status change
                if ($order->user && in_array($status, ['processing', 'shipped', 'delivered', 'cancelled'], true)) {
                    $order->user->notify(new OrderStatusUpdatedNotification($order, $status, $vendorOrder));
                    if ($order->user->fcm_token) {
                        SendFirebaseNotification::dispatch(
                            $order->user->fcm_token,
                            'Your order status has been updated',
                            'Your order with id #'.$order->id.' has been updated to '.$status,
                            [
                                'order_id' => (string) $order->id,
                                'order_status' => $status,
                            ]
                        );
                    }
                }

                // Log vendor order status change
                OrderLog::create([
                    'order_id' => $order->id,
                    'vendor_order_id' => $vendorOrder->id,
                    'user_id' => Auth::id(),
                    'type' => 'vendor_status_change',
                    'from_status' => $currentStatus,
                    'to_status' => $status,
                    'payload' => null,
                ]);
            }

            return true;
        });
    }

    /**
     * Calculate shipping costs for the user's cart without creating an order.
     *
     * @return array{
     *   total_shipping: float,
     *   vendors: array<int, array{
     *     vendor_id: int,
     *     vendor_name: string,
     *     branch_id: int|null,
     *     branch_name: string|null,
     *     distance_km: float,
     *     shipping_cost: float,
     *     subtotal: float,
     *     free_shipping_threshold: float|null,
     *     is_free_shipping: bool
     *   }>
     * }
     */
    public function calculateShippingCost(int $userId, int $addressId): array
    {
        $user = User::query()->findOrFail($userId);

        // Address must belong to the user
        /** @var Address|null $address */
        $address = Address::query()->where('user_id', $userId)->find($addressId);
        if (! $address) {
            throw ValidationException::withMessages([
                'address_id' => [__('Invalid address.')],
            ]);
        }

        // Get cart items
        $cartItems = $this->cartRepository->getUserCartItems($userId);
        if ($cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => [__('Your cart is empty.')],
            ]);
        }

        $this->primeOrderLookupCaches($cartItems);

        // Validate and filter items
        $validItems = collect();
        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;
            $variant = $cartItem->variant;

            if (! $product || ! $product->is_active) {
                continue;
            }

            if ($product->type === 'variable') {
                if (! $variant || ! $variant->is_active) {
                    continue;
                }
            }

            $validItems->push($cartItem);
        }

        if ($validItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => [__('All items in your cart are no longer available.')],
            ]);
        }

        // Group items by vendor and calculate vendor subtotals
        $groupedByVendor = $validItems->groupBy(fn ($item) => $item->product->vendor_id);
        $vendorShippingData = [];
        $totalShipping = 0.0;

        foreach ($groupedByVendor as $vendorId => $items) {
            $vendorSubtotal = 0.0;

            foreach ($items as $item) {
                $product = $item->product;
                $variant = $item->variant;

                // Price after product discount
                $unitPrice = $this->calculateUnitPrice($product, $variant);
                $vendorSubtotal += $unitPrice * (int) $item->quantity;
            }

            // Select branch for vendor
            /** @var Branch|null $branch */
            $branch = $this->selectBranchForVendor($vendorId, $items, $address);

            // Calculate shipping cost
            $shippingCost = $this->calculateVendorShipping($vendorId, $branch, $address, $vendorSubtotal);

            $settings = $this->getVendorSettings($vendorId);
            $allowFreeThreshold = (bool) ($settings['allow_free_shipping_threshold']->value ?? false);
            $minForFree = (float) ($settings['minimum_order_amount_for_free_shipping']->value ?? 0);
            $isFreeShipping = $allowFreeThreshold && $vendorSubtotal >= $minForFree;

            // Calculate distance
            $distance = $this->distanceKm(
                (float) $address->latitude,
                (float) $address->longitude,
                (float) ($branch->latitude ?? 0),
                (float) ($branch->longitude ?? 0)
            );

            $vendor = $this->getVendorForCommission((int) $vendorId);

            $vendorShippingData[] = [
                'vendor_id' => $vendorId,
                'vendor_name' => $vendor?->name ?? __('Vendor #:id', ['id' => $vendorId]),
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'distance_km' => round($distance, 2),
                'shipping_cost' => round($shippingCost, 2),
                'subtotal' => round($vendorSubtotal, 2),
                'free_shipping_threshold' => $allowFreeThreshold ? round($minForFree, 2) : null,
                'is_free_shipping' => $isFreeShipping,
            ];

            $totalShipping += $shippingCost;
        }

        return [
            'total_shipping' => round($totalShipping, 2),
            'vendors' => $vendorShippingData,
        ];
    }

    /**
     * Create a new order from the user's cart (the ONLY way to create orders).
     *
     * This follows the order cycle:
     *  - Cart → validate items/stock → coupon → wallet/points → branch selection → shipping
     *  - Create orders/vendor_orders/vendor_order_items → deduct inventory → log wallet/points → clear cart
     *
     * @param  array<string, mixed>  $data
     */
    public function createOrder(int $userId, array $data): Order
    {
        return DB::transaction(function () use ($userId, $data) {
            $user = User::query()->lockForUpdate()->findOrFail($userId);

            // Address must belong to the user
            /** @var Address|null $address */
            $address = Address::query()->where('user_id', $userId)->find($data['address_id']);
            if (! $address) {
                throw ValidationException::withMessages([
                    'address_id' => [__('Invalid address.')],
                ]);
            }

            // 1. Start with User Cart
            $cartItems = $this->cartRepository->getUserCartItems($userId);
            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => [__('Your cart is empty.')],
                ]);
            }

            $this->primeOrderLookupCaches($cartItems);

            // 2. Validate Products (basic availability + active)
            $validItems = collect();
            $invalidItemIds = [];

            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;
                $variant = $cartItem->variant;

                if (! $product || ! $product->is_active) {
                    $invalidItemIds[] = $cartItem->id;
                    if ($product) {
                        $this->cartRepository->removeItem($userId, $product, $cartItem->variant_id);
                    }

                    continue;
                }

                if ($product->type === 'variable') {
                    if (! $variant || ! $variant->is_active) {
                        $invalidItemIds[] = $cartItem->id;
                        $this->cartRepository->removeItem($userId, $product, $cartItem->variant_id);

                        continue;
                    }
                }

                $validItems->push($cartItem);
            }

            if ($validItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => [__('All items in your cart are no longer available.')],
                ]);
            }

            if (! empty($invalidItemIds)) {
                throw ValidationException::withMessages([
                    'cart' => [__('Some items were removed from your cart because they are no longer available. Please review your cart and try again.')],
                ]);
            }

            // Group items by vendor and calculate vendor subtotals
            $groupedByVendor = $validItems->groupBy(fn ($item) => $item->product->vendor_id);
            $vendorData = [];
            $orderSubTotal = 0.0;
            $originalTotal = 0.0; // Total before product discounts

            foreach ($groupedByVendor as $vendorId => $items) {
                $vendorSubtotal = 0.0;
                $vendorOriginalTotal = 0.0;

                // Calculate initial subtotal (will be recalculated with branch prices later)
                foreach ($items as $item) {
                    $product = $item->product;
                    $variant = $item->variant;

                    // Original price (before discount)
                    $originalUnitPrice = $variant ? (float) $variant->price : (float) $product->price;
                    $vendorOriginalTotal += $originalUnitPrice * (int) $item->quantity;

                    // Price after product discount (without branch price for now)
                    $unitPrice = $this->calculateUnitPrice($product, $variant);
                    $vendorSubtotal += $unitPrice * (int) $item->quantity;
                }

                $vendorData[(int) $vendorId] = [
                    'items' => $items,
                    'subtotal' => $vendorSubtotal,
                    'discount' => 0.0,
                    'shipping_cost' => 0.0,
                    'total' => 0.0,
                    'branch_id' => null,
                    'commission' => 0.0,
                ];

                $orderSubTotal += $vendorSubtotal;
                $originalTotal += $vendorOriginalTotal;
            }

            // Calculate total product discount
            $productDiscount = round($originalTotal - $orderSubTotal, 2);

            // 3. Apply Coupons
            $coupon = null;
            $couponDiscount = 0.0;
            if (! empty($data['coupon_id'])) {
                $coupon = Coupon::query()->find($data['coupon_id']);

                if (! $coupon || ! $coupon->isValid()) {
                    throw ValidationException::withMessages([
                        'coupon_id' => [__('Coupon is invalid or expired.')],
                    ]);
                }

                if ($orderSubTotal < (float) $coupon->min_cart_amount) {
                    throw ValidationException::withMessages([
                        'coupon_id' => [__('Cart total does not meet the minimum amount for this coupon.')],
                    ]);
                }

                if ($coupon->usage_limit_per_user !== null && ! $coupon->isUsableByUser($user)) {
                    throw ValidationException::withMessages([
                        'coupon_id' => [__('Coupon usage limit reached.')],
                    ]);
                }

                if ($coupon->type === 'percentage') {
                    $couponDiscount = round(($orderSubTotal * (float) $coupon->discount_value) / 100, 2);
                } else {
                    $couponDiscount = min((float) $coupon->discount_value, $orderSubTotal);
                }
            }

            // Distribute coupon discount across vendors proportionally
            if ($couponDiscount > 0 && $orderSubTotal > 0) {
                foreach ($vendorData as $vendorId => $vData) {
                    $ratio = $vData['subtotal'] / $orderSubTotal;
                    $vendorData[$vendorId]['discount'] = round($couponDiscount * $ratio, 2);
                }
            }

            // 5. Determine Vendor Branches (choose nearest branch with enough stock)
            foreach ($vendorData as $vendorId => $vData) {
                /** @var Branch|null $branch */
                $branch = $this->selectBranchForVendor($vendorId, $vData['items'], $address);

                if (! $branch) {
                    throw ValidationException::withMessages([
                        'cart' => [__('Some items are out of stock for vendor: :vendor', ['vendor' => $vendorId])],
                    ]);
                }

                $vendorData[$vendorId]['branch_id'] = $branch->id;

                // Recalculate subtotal with branch-specific prices
                $vendorSubtotal = 0.0;
                foreach ($vData['items'] as $item) {
                    $product = $item->product;
                    $variant = $item->variant;
                    $unitPrice = $this->calculateUnitPrice($product, $variant, $branch->id);
                    $vendorSubtotal += $unitPrice * (int) $item->quantity;
                }
                $vendorData[$vendorId]['subtotal'] = $vendorSubtotal;

                // 6. Calculate Shipping Costs (vendor settings based)
                $vendorData[$vendorId]['shipping_cost'] = $this->calculateVendorShipping($vendorId, $branch, $address, (float) $vData['subtotal']);
            }

            $totalShipping = array_sum(array_map(fn ($v) => (float) $v['shipping_cost'], $vendorData));

            // Vendor order totals (subtotal - discount + shipping)
            foreach ($vendorData as $vendorId => $vData) {
                $vendorData[$vendorId]['total'] = round(
                    (float) $vData['subtotal'] - (float) $vData['discount'] + (float) $vData['shipping_cost'],
                    2
                );
            }

            // 4. Apply Loyalty Points / Wallet (cap by balances)
            // order_discount now stores the total product discount amount
            $orderDiscount = $productDiscount;

            $totalBeforeWalletPoints = round($orderSubTotal - $orderDiscount - $couponDiscount + $totalShipping, 2);
            if ($totalBeforeWalletPoints < 0) {
                $totalBeforeWalletPoints = 0.0;
            }

            // Handle wallet: if use_wallet is true, use all available; otherwise 0
            $useWallet = ($data['use_wallet'] ?? false) == true;
            if ($useWallet) {
                $walletUsed = min((float) $user->wallet, $totalBeforeWalletPoints);
            } else {
                $walletUsed = 0;
            }

            $remainingAfterWallet = $totalBeforeWalletPoints - $walletUsed;

            // Handle points: if use_points is true, use all available; otherwise 0
            $usePoints = ($data['use_points'] ?? false) === true;
            if ($usePoints) {
                $pointsUsed = min((float) $user->points, $remainingAfterWallet);
            } else {
                $pointsUsed = 0;
            }

            // 7. Create Main Order
            $order = $this->orderRepository->create([
                'user_id' => $userId,
                'sub_total' => $orderSubTotal,
                'order_discount' => $orderDiscount,
                'coupon_id' => $coupon?->id,
                'coupon_discount' => $couponDiscount,
                'total_shipping' => $totalShipping,
                'points_discount' => $pointsUsed,
                'wallet_used' => $walletUsed,
                'total' => $totalBeforeWalletPoints,
                'status' => $data['status'] ?? 'pending',
                'payment_method' => $data['payment_method'] ?? 'COD',
                'payment_status' => $data['payment_status'] ?? 'pending',
                'notes' => $data['notes'] ?? null,
                'address_id' => $address->id,
                'total_commission' => 0,
            ]);

            // 8 & 9. Create Vendor Orders & Vendor Order Items
            $profitType = setting('profit_type');
            $totalCommission = 0.0;

            foreach ($vendorData as $vendorId => $vData) {
                $vendor = $this->getVendorForCommission($vendorId);
                $commission = 0.0;

                if ($profitType === 'commission' && $vendor) {
                    $commissionBase = max(0, (float) $vData['subtotal'] - (float) $vData['discount']);
                    $commission = round(($commissionBase * (float) ($vendor->commission_rate ?? 0)) / 100, 2);
                }

                $vendorData[$vendorId]['commission'] = $commission;
                $totalCommission += $commission;

                $vendorOrder = VendorOrder::create([
                    'order_id' => $order->id,
                    'vendor_id' => $vendorId,
                    'branch_id' => $vData['branch_id'],
                    'sub_total' => $vData['subtotal'],
                    'discount' => $vData['discount'],
                    'shipping_cost' => $vData['shipping_cost'],
                    'total' => $vData['total'],
                    'status' => 'pending',
                    'notes' => null,
                    'commission' => $commission,
                ]);

                // 10. Update Inventory (deduct stock per selected branch)
                $this->deductStockForVendorOrderItems((int) $vData['branch_id'], $vData['items']);

                foreach ($vData['items'] as $item) {
                    $product = $item->product;
                    $variant = $item->variant;

                    $unitPrice = $this->calculateUnitPrice($product, $variant, $vData['branch_id']);
                    $lineTotal = $unitPrice * (int) $item->quantity;

                    VendorOrderItem::create([
                        'vendor_order_id' => $vendorOrder->id,
                        'product_id' => $product->id,
                        'variant_id' => $variant?->id,
                        'price' => $unitPrice,
                        'quantity' => (int) $item->quantity,
                        'total' => $lineTotal,
                        'notes' => null,
                    ]);
                }
            }

            // Persist total commission on main order
            $this->orderRepository->update($order, ['total_commission' => round($totalCommission, 2)]);

            // 11. Wallet / Loyalty transactions
            // Only deduct and log transactions if wallet/points were actually used
            if ($walletUsed > 0) {
                $user->wallet = round((float) $user->wallet - $walletUsed, 2);
                $user->save();

                WalletTransaction::create([
                    'user_id' => $userId,
                    'type' => 'subtraction',
                    'amount' => $walletUsed,
                    'balance_after' => $user->wallet,
                    'notes' => 'Order #'.$order->id,
                ]);
            }

            if ($pointsUsed > 0) {
                $user->points = round((float) $user->points - $pointsUsed, 2);
                $user->save();

                PointTransaction::create([
                    'user_id' => $userId,
                    'type' => 'subtraction',
                    'amount' => (int) round($pointsUsed),
                    'balance_after' => (int) round($user->points),
                    'notes' => 'Order #'.$order->id,
                ]);
            } else {
                // If user didn't use points, add cashback points based on order total
                if (! $usePoints) {
                    $cashbackRate = (float) setting('cache_back_points_rate', 0);
                    if ($cashbackRate > 0) {
                        // Calculate cashback points: (order total * rate) / 100
                        $cashbackPoints = round(($totalBeforeWalletPoints * $cashbackRate) / 100, 0);
                        if ($cashbackPoints > 0) {
                            $user->points = round((float) $user->points + $cashbackPoints, 2);
                            $user->save();

                            PointTransaction::create([
                                'user_id' => $userId,
                                'type' => 'addition',
                                'amount' => (int) $cashbackPoints,
                                'balance_after' => (int) round($user->points),
                                'notes' => 'Cashback for Order #'.$order->id,
                            ]);
                        }
                    }
                }
            }

            // 13. Clear Cart
            $this->cartRepository->clearCart($userId);

            // 12. Payment Processing
            // NOTE: payment_status is set to 'pending' by default.
            // The remaining amount to charge via a gateway is exposed as $order->payable_amount
            // (total - wallet_used - points_discount); Fawry links use it in OrderController.

            // 14. Notifications (to be implemented later)
            $orderId = (int) $order->id;

            DB::afterCommit(function () use ($orderId) {
                $order = $this->getOrderById($orderId);

                if (! $order) {
                    return;
                }

                $order->loadMissing(['user', 'vendorOrders']);

                // Customer
                $this->notificationService->notifyUser($order->user, new OrderPlacedNotification($order));

                // Vendors (per vendor order)
                foreach ($order->vendorOrders as $vendorOrder) {
                    $this->notificationService->notifyVendorUsers(
                        (int) $vendorOrder->vendor_id,
                        new NewVendorOrderNotification($order, $vendorOrder)
                    );
                }

                // Admins
                $this->notificationService->notifyAdmins(new NewOrderForAdminNotification($order));
            });

            return $order->load([
                'user',
                'coupon',
                'address',
                'vendorOrders.vendor' => fn ($vendorQuery) => Vendor::constrainVisibleRatingAggregates($vendorQuery),
                'vendorOrders.branch',
                'vendorOrders.items.product' => fn ($productQuery) => \App\Models\Product::constrainApiVisible(
                    $productQuery->with(\App\Models\Product::apiListRelations())
                ),
                'vendorOrders.items.variant',
            ]);
        });
    }

    /**
     * Calculate the final unit price for a cart/order item
     * based on variant or product price and product discount.
     */
    private function calculateUnitPrice($product, $variant = null, ?int $branchId = null): float
    {
        // If branch_id is provided, try to get branch-specific price from stock records
        if ($branchId) {
            if ($variant) {
                // For variants, check branch variant stock record
                $branchStock = $this->findVariantStock($branchId, (int) $variant->id);

                if ($branchStock && $branchStock->price !== null) {
                    $unitPrice = (float) $branchStock->price;
                    $discount = (float) ($branchStock->discount ?? 0);
                    $discountType = $branchStock->discount_type ?? null;

                    if ($discount > 0) {
                        if ($discountType === 'percentage') {
                            $unitPrice -= ($unitPrice * $discount) / 100;
                        } else {
                            $unitPrice = (float) max(0, $unitPrice - $discount);
                        }
                    }

                    return $unitPrice;
                }
            } else {
                // For simple products, check branch product stock record
                $branchStock = $this->findProductStock($branchId, (int) $product->id);

                if ($branchStock && $branchStock->price !== null) {
                    $unitPrice = (float) $branchStock->price;
                    $discount = (float) ($branchStock->discount ?? 0);
                    $discountType = $branchStock->discount_type ?? null;

                    if ($discount > 0) {
                        if ($discountType === 'percentage') {
                            $unitPrice -= ($unitPrice * $discount) / 100;
                        } else {
                            $unitPrice = (float) max(0, $unitPrice - $discount);
                        }
                    }

                    return $unitPrice;
                }
            }
        }

        // Fall back to default product/variant price
        $unitPrice = (float) ($variant?->price ?? $product->price);

        $discount = (float) ($product->discount ?? 0);
        $discountType = $product->discount_type ?? null;

        if ($discount > 0) {
            if ($discountType === 'percentage') {
                $unitPrice -= ($unitPrice * $discount) / 100;
            } else {
                $unitPrice = (float) max(0, $unitPrice - $discount);
            }
        }

        return $unitPrice;
    }

    private function getVendorForCommission(int $vendorId): ?Vendor
    {
        if ($this->vendorsById !== null) {
            return $this->vendorsById[$vendorId] ?? null;
        }

        return Vendor::query()->find($vendorId);
    }

    private function selectBranchForVendor(int $vendorId, $items, Address $address): ?Branch
    {
        $branches = $this->getActiveBranchesForVendor($vendorId);

        if ($branches->isEmpty()) {
            return null;
        }

        $candidates = [];

        foreach ($branches as $branch) {
            if ($this->branchCanFulfillItems($branch->id, $items)) {
                $distance = $this->distanceKm(
                    (float) $address->latitude,
                    (float) $address->longitude,
                    (float) ($branch->latitude ?? 0),
                    (float) ($branch->longitude ?? 0)
                );
                $candidates[] = ['branch' => $branch, 'distance' => $distance];
            }
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, fn ($a, $b) => $a['distance'] <=> $b['distance']);

        return $candidates[0]['branch'];
    }

    private function branchCanFulfillItems(int $branchId, $items): bool
    {
        foreach ($items as $item) {
            $product = $item->product;
            $variant = $item->variant;
            $qty = (int) $item->quantity;

            if ($variant) {
                $stock = $this->findVariantStock($branchId, (int) $variant->id);

                if (! $stock || (int) $stock->quantity < $qty) {
                    return false;
                }
            } else {
                $stock = $this->findProductStock($branchId, (int) $product->id);

                if (! $stock || (int) $stock->quantity < $qty) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Prefetch branches, vendors, settings and stocks for the current cart to avoid N+1 loops.
     */
    private function primeOrderLookupCaches(Collection $cartItems): void
    {
        $vendorIds = $cartItems
            ->map(fn ($item) => $item->product?->vendor_id)
            ->filter()
            ->unique()
            ->values();

        $productIds = $cartItems
            ->map(fn ($item) => $item->product_id)
            ->filter()
            ->unique()
            ->values();

        $variantIds = $cartItems
            ->map(fn ($item) => $item->variant_id)
            ->filter()
            ->unique()
            ->values();

        if ($vendorIds->isEmpty()) {
            $this->vendorsById = [];
            $this->activeBranchesByVendorId = [];
            $this->vendorSettingsByVendorId = [];
            $this->productStocksByKey = [];
            $this->variantStocksByKey = [];

            return;
        }

        $this->vendorsById = Vendor::query()
            ->whereIn('id', $vendorIds)
            ->get()
            ->keyBy('id')
            ->all();

        $this->activeBranchesByVendorId = Branch::query()
            ->whereIn('vendor_id', $vendorIds)
            ->where('is_active', true)
            ->get()
            ->groupBy('vendor_id')
            ->all();

        $this->vendorSettingsByVendorId = VendorSetting::query()
            ->whereIn('vendor_id', $vendorIds)
            ->whereIn('key', [
                'allow_free_shipping_threshold',
                'minimum_order_amount_for_free_shipping',
                'shipping_cost_per_km',
                'minimum_shipping_cost',
                'maximum_shipping_cost',
            ])
            ->get()
            ->groupBy('vendor_id')
            ->map(fn (Collection $settings) => $settings->keyBy('key'))
            ->all();

        $branchIds = collect($this->activeBranchesByVendorId)
            ->flatten()
            ->pluck('id')
            ->unique()
            ->values();

        $this->productStocksByKey = $branchIds->isEmpty() || $productIds->isEmpty()
            ? []
            : BranchProductStock::query()
                ->whereIn('branch_id', $branchIds)
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy(fn (BranchProductStock $stock) => $stock->branch_id.':'.$stock->product_id)
                ->all();

        $this->variantStocksByKey = $branchIds->isEmpty() || $variantIds->isEmpty()
            ? []
            : BranchProductVariantStock::query()
                ->whereIn('branch_id', $branchIds)
                ->whereIn('product_variant_id', $variantIds)
                ->get()
                ->keyBy(fn (BranchProductVariantStock $stock) => $stock->branch_id.':'.$stock->product_variant_id)
                ->all();
    }

    private function getActiveBranchesForVendor(int $vendorId): \Illuminate\Support\Collection
    {
        if ($this->activeBranchesByVendorId !== null) {
            return $this->activeBranchesByVendorId[$vendorId] ?? collect();
        }

        return Branch::query()
            ->where('vendor_id', $vendorId)
            ->where('is_active', true)
            ->get();
    }

    private function getVendorSettings(int $vendorId): \Illuminate\Support\Collection
    {
        if ($this->vendorSettingsByVendorId !== null) {
            return $this->vendorSettingsByVendorId[$vendorId] ?? collect();
        }

        return VendorSetting::query()
            ->where('vendor_id', $vendorId)
            ->whereIn('key', [
                'allow_free_shipping_threshold',
                'minimum_order_amount_for_free_shipping',
                'shipping_cost_per_km',
                'minimum_shipping_cost',
                'maximum_shipping_cost',
            ])
            ->get()
            ->keyBy('key');
    }

    private function findProductStock(int $branchId, int $productId): ?BranchProductStock
    {
        $key = $branchId.':'.$productId;

        if ($this->productStocksByKey !== null) {
            return $this->productStocksByKey[$key] ?? null;
        }

        return BranchProductStock::query()
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->first();
    }

    private function findVariantStock(int $branchId, int $variantId): ?BranchProductVariantStock
    {
        $key = $branchId.':'.$variantId;

        if ($this->variantStocksByKey !== null) {
            return $this->variantStocksByKey[$key] ?? null;
        }

        return BranchProductVariantStock::query()
            ->where('branch_id', $branchId)
            ->where('product_variant_id', $variantId)
            ->first();
    }

    private function deductStockForVendorOrderItems(int $branchId, $items): void
    {
        foreach ($items as $item) {
            $product = $item->product;
            $variant = $item->variant;
            $qty = (int) $item->quantity;

            if ($variant) {
                $stock = BranchProductVariantStock::query()
                    ->where('branch_id', $branchId)
                    ->where('product_variant_id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock || (int) $stock->quantity < $qty) {
                    throw ValidationException::withMessages([
                        'cart' => [__('Some items are out of stock. Please review your cart and try again.')],
                    ]);
                }

                $stock->decrement('quantity', $qty, []);
                $stock->refresh();

                DB::afterCommit(function () use ($stock, $variant) {
                    $variant->loadMissing('product');
                    $this->inventoryAlertService->checkVariantStock($stock, $variant);
                });
            } else {
                $stock = BranchProductStock::query()
                    ->where('branch_id', $branchId)
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock || (int) $stock->quantity < $qty) {
                    throw ValidationException::withMessages([
                        'cart' => [__('Some items are out of stock. Please review your cart and try again.')],
                    ]);
                }

                $stock->decrement('quantity', $qty, []);
                $stock->refresh();

                DB::afterCommit(function () use ($stock, $product) {
                    $this->inventoryAlertService->checkSimpleStock($stock, $product);
                });
            }
        }
    }

    private function restoreStockForVendorOrderItems(int $branchId, $items): void
    {
        foreach ($items as $item) {
            $product = $item->product;
            $variant = $item->variant;
            $qty = (int) $item->quantity;

            if ($variant) {
                $stock = BranchProductVariantStock::query()
                    ->where('branch_id', $branchId)
                    ->where('product_variant_id', $variant->id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->increment('quantity', $qty, []);
                }
            } else {
                $stock = BranchProductStock::query()
                    ->where('branch_id', $branchId)
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->increment('quantity', $qty, []);
                }
            }
        }
    }

    private function calculateVendorShipping(int $vendorId, Branch $branch, Address $address, float $vendorSubtotal): float
    {
        $settings = $this->getVendorSettings($vendorId);

        $allowFreeThreshold = (bool) ($settings['allow_free_shipping_threshold']->value ?? false);
        $minForFree = (float) ($settings['minimum_order_amount_for_free_shipping']->value ?? 0);
        $costPerKm = (float) ($settings['shipping_cost_per_km']->value ?? 0);
        $minCost = (float) ($settings['minimum_shipping_cost']->value ?? 0);
        $maxCost = (float) ($settings['maximum_shipping_cost']->value ?? 0);

        if ($allowFreeThreshold && $vendorSubtotal >= $minForFree) {
            return 0.0;
        }

        $distance = $this->distanceKm(
            (float) $address->latitude,
            (float) $address->longitude,
            (float) ($branch->latitude ?? 0),
            (float) ($branch->longitude ?? 0)
        );

        $shipping = round($distance * $costPerKm, 2);

        if ($minCost > 0) {
            $shipping = max($shipping, $minCost);
        }

        if ($maxCost > 0) {
            $shipping = min($shipping, $maxCost);
        }

        return max(0.0, $shipping);
    }

    private function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        if ($lat1 === 0.0 && $lon1 === 0.0) {
            return 0.0;
        }

        if ($lat2 === 0.0 && $lon2 === 0.0) {
            return 0.0;
        }

        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Update an order.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateOrder(Order $order, array $data): bool
    {
        return DB::transaction(function () use ($order, $data) {
            return $this->orderRepository->update($order, $data);
        });
    }

    /**
     * Update order status.
     */
    public function updateStatus(Order $order, string $status): bool
    {
        return DB::transaction(function () use ($order, $status) {
            $previousStatus = $order->status;
            // If cancelling the order, cancel all associated vendor orders
            if ($status === 'cancelled') {
                VendorOrder::query()
                    ->where('order_id', $order->id)
                    ->whereIn('status', ['pending', 'processing'])
                    ->update(['status' => 'cancelled']);
            }

            $updated = $this->updateOrder($order, ['status' => $status]);

            if ($updated) {
                // Log status change
                OrderLog::create([
                    'order_id' => $order->id,
                    'vendor_order_id' => null,
                    'user_id' => Auth::id(),
                    'type' => 'order_status_change',
                    'from_status' => $previousStatus,
                    'to_status' => $status,
                    'payload' => null,
                ]);

                if ($order->user && in_array($status, ['processing', 'shipped', 'delivered', 'cancelled'], true)) {
                    $order->user->notify(new OrderStatusUpdatedNotification($order, $status));
                    if ($order->user->fcm_token) {
                        SendFirebaseNotification::dispatch(
                            $order->user->fcm_token,
                            'Your order status has been updated',
                            'Your order with id #'.$order->id.' has been updated to '.$status,
                            [
                                'order_id' => (string) $order->id,
                                'order_status' => $status,
                            ]
                        );
                    }
                }
            }

            return $updated;
        });
    }

    /**
     * Soft delete an order.
     */
    public function deleteOrder(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            return $this->orderRepository->delete($order);
        });
    }

    /**
     * Cancel an order for a specific user (if cancellable).
     * Refunds wallet and points used, and reverses cashback points if added.
     *
     * Note: Commission is a historical snapshot calculated at order creation time.
     * Cancelling does not change `vendor_orders.commission` nor `orders.total_commission`.
     */
    public function cancelOrderForUser(int $orderId, int $userId): ?Order
    {
        return DB::transaction(function () use ($orderId, $userId) {
            $order = $this->getOrderByIdForUser($orderId, $userId);

            if (! $order) {
                return null;
            }
            if (! in_array($order->status, ['pending', 'processing'], true)) {
                return $order;
            }

            $user = User::query()->lockForUpdate()->findOrFail($userId);

            $walletUsed = (float) $order->wallet_used;
            $isPaid = ($order->payment_status ?? null) == 'paid';
            $refundAmount = $isPaid ? (float) $order->total : $walletUsed;

            if ($refundAmount > 0) {
                $user->wallet = round((float) $user->wallet + $refundAmount, 2);
                $user->save();

                WalletTransaction::create([
                    'user_id' => $userId,
                    'type' => 'addition',
                    'amount' => $refundAmount,
                    'balance_after' => $user->wallet,
                    'notes' => 'Refund for cancelled Order #'.$order->id,
                ]);
            }

            if ($isPaid) {
                $this->orderRepository->update($order, [
                    'payment_status' => 'refunded',
                    'refund_status' => 'refunded',
                    'refunded_total' => $refundAmount,
                ]);
            }

            // Handle points: refund if used, or reverse cashback if added
            $pointsDiscount = (float) $order->points_discount;
            if ($pointsDiscount > 0) {
                // Points were used - refund them
                $user->points = round((float) $user->points + $pointsDiscount, 2);
                $user->save();

                PointTransaction::create([
                    'user_id' => $userId,
                    'type' => 'addition',
                    'amount' => (int) round($pointsDiscount),
                    'balance_after' => (int) round($user->points),
                    'notes' => 'Refund for cancelled Order #'.$order->id,
                ]);
            } else {
                // Points were not used - check if cashback was added
                $cashbackTransaction = PointTransaction::query()
                    ->where('user_id', $userId)
                    ->where('type', 'addition')
                    ->where('notes', 'Cashback for Order #'.$order->id)
                    ->first();

                if ($cashbackTransaction) {
                    // Reverse cashback points
                    $cashbackAmount = (int) $cashbackTransaction->amount;
                    $user->points = max(0, round((float) $user->points - $cashbackAmount, 2));
                    $user->save();

                    PointTransaction::create([
                        'user_id' => $userId,
                        'type' => 'subtraction',
                        'amount' => $cashbackAmount,
                        'balance_after' => (int) round($user->points),
                        'notes' => 'Cashback reversed for cancelled Order #'.$order->id,
                    ]);
                }
            }

            // Restore stock and cancel vendor orders
            $order->loadMissing(['vendorOrders.items.product', 'vendorOrders.items.variant']);

            foreach ($order->vendorOrders as $vendorOrder) {
                // Stock was only deducted when vendor order moved to processing
                if ($vendorOrder->status === 'processing' && $vendorOrder->branch_id) {
                    $this->restoreStockForVendorOrderItems((int) $vendorOrder->branch_id, $vendorOrder->items);
                }
            }

            VendorOrder::query()
                ->where('order_id', $order->id)
                ->whereIn('status', ['pending', 'processing'])
                ->update(['status' => 'cancelled']);

            // Update order status to cancelled
            $this->updateStatus($order, 'cancelled');

            return $order->fresh();
        });
    }

    /**
     * Refund a delivered order (wallet only, keep points/cashback).
     *
     * Note: Commission is a historical snapshot calculated at order creation time.
     * Refunding does not change `vendor_orders.commission` nor `orders.total_commission`.
     */
    public function refundOrder(Order $order, int $userId): Order
    {
        return DB::transaction(function () use ($order, $userId) {
            if ($order->status !== 'delivered') {
                throw ValidationException::withMessages([
                    'order' => [__('Only delivered orders can be refunded.')],
                ]);
            }

            if ($order->refund_status === 'refunded') {
                return $order->fresh();
            }

            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $walletUsed = (float) $order->wallet_used;
            // Refund order payment if paid
            if ($order->payment_status === 'paid') {
                $order->payment_status = 'refunded';
                $order->refund_status = 'refunded';
                $order->save();
                $user->wallet = round((float) $user->wallet + $order->total, 2);
                $user->save();

                WalletTransaction::create([
                    'user_id' => $userId,
                    'type' => 'addition',
                    'amount' => $order->total,
                    'balance_after' => $user->wallet,
                    'notes' => 'Refund for Order #'.$order->id,
                ]);
            } elseif ($walletUsed > 0) {
                $user->wallet = round((float) $user->wallet + $walletUsed, 2);
                $user->save();

                WalletTransaction::create([
                    'user_id' => $userId,
                    'type' => 'addition',
                    'amount' => $walletUsed,
                    'balance_after' => $user->wallet,
                    'notes' => 'Refund for Order #'.$order->id,
                ]);
            }
            // For refunds initiated by admin/vendor we KEEP points/cashback as is

            // Restore stock for delivered vendor orders
            $order->loadMissing(['vendorOrders.items.product', 'vendorOrders.items.variant']);

            foreach ($order->vendorOrders as $vendorOrder) {
                if ($vendorOrder->status === 'delivered' && $vendorOrder->branch_id) {
                    $this->restoreStockForVendorOrderItems((int) $vendorOrder->branch_id, $vendorOrder->items);
                    $vendorOrder->status = 'cancelled';
                    $vendorOrder->save();
                }
            }

            $this->orderRepository->update($order, [
                'refund_status' => 'refunded',
                'refunded_total' => $order->total,
            ]);

            return $order->fresh();
        });
    }

    /**
     * Recalculate and persist total commission for an order based on vendor orders.
     */
    public function refreshTotalCommission(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $totalCommission = (float) $order->vendorOrders()->sum('commission');

            $this->orderRepository->update($order, [
                'total_commission' => $totalCommission,
            ]);

            return $order->fresh();
        });
    }

    /**
     * Reorder items from a previous order by adding them to the user's cart.
     *
     * @return array{success: bool, added: array, skipped: array, message: string}
     */
    public function reorder(int $orderId, int $userId): array
    {
        $order = $this->getOrderByIdForUser($orderId, $userId);

        if (! $order) {
            throw ValidationException::withMessages([
                'order_id' => [__('Order not found.')],
            ]);
        }

        // Load vendor orders with items and products
        $order->load([
            'vendorOrders.items.product',
            'vendorOrders.items.variant',
        ]);

        $added = [];
        $skipped = [];

        foreach ($order->vendorOrders as $vendorOrder) {
            foreach ($vendorOrder->items as $item) {
                $product = $item->product;
                $variant = $item->variant;

                // Check if product still exists and is active
                if (! $product || ! $product->is_active) {
                    $skipped[] = [
                        'product_id' => $item->product_id,
                        'product_name' => $product?->name ?? __('Product not found'),
                        'variant_id' => $item->variant_id,
                        'quantity' => $item->quantity,
                        'reason' => __('Product is no longer available.'),
                    ];

                    continue;
                }

                // For variable products, check if variant exists and is active
                if ($product->type === 'variable') {
                    if (! $variant || ! $variant->is_active) {
                        $skipped[] = [
                            'product_id' => $item->product_id,
                            'product_name' => $product->name,
                            'variant_id' => $item->variant_id,
                            'quantity' => $item->quantity,
                            'reason' => __('Product variant is no longer available.'),
                        ];

                        continue;
                    }
                }

                try {
                    // Add product to cart (adds 1 or increments if exists)
                    $cartItem = $this->cartService->addProduct($userId, $product, $item->variant_id);

                    // Update quantity to match the order item quantity
                    if ($cartItem->quantity !== $item->quantity) {
                        $this->cartService->updateProductQuantity($userId, $product, $item->quantity, $item->variant_id);
                    }

                    $added[] = [
                        'product_id' => $item->product_id,
                        'product_name' => $product->name,
                        'variant_id' => $item->variant_id,
                        'variant_name' => $variant?->name,
                        'quantity' => $item->quantity,
                    ];
                } catch (\Exception $e) {
                    $skipped[] = [
                        'product_id' => $item->product_id,
                        'product_name' => $product->name,
                        'variant_id' => $item->variant_id,
                        'quantity' => $item->quantity,
                        'reason' => __('Failed to add to cart: :message', ['message' => $e->getMessage()]),
                    ];
                }
            }
        }

        $message = __('Reorder completed.');
        if (count($skipped) > 0) {
            $message = __('Some items could not be added to cart. Please review your cart.');
        }

        return [
            'success' => true,
            'added' => $added,
            'skipped' => $skipped,
            'message' => $message,
        ];
    }
}
