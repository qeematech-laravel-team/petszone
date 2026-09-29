<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBalanceTransaction;
use App\Models\VendorWithdrawal;
use App\Notifications\VendorWithdrawalStatusUpdatedNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VendorWithdrawalService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function listForAdmin(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = VendorWithdrawal::query()
            ->with(['vendor', 'processor'])
            ->orderByDesc('id');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['vendor_id'])) {
            $query->where('vendor_id', (int) $filters['vendor_id']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function listForVendor(int $vendorId, int $perPage = 15): LengthAwarePaginator
    {
        return VendorWithdrawal::query()
            ->where('vendor_id', $vendorId)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createRequestForVendor(Vendor $vendor, float $amount, ?string $method = null, ?string $notes = null): VendorWithdrawal
    {
        return DB::transaction(function () use ($vendor, $amount, $method, $notes) {
            $vendor = Vendor::query()->lockForUpdate()->findOrFail($vendor->id);

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => __('Amount must be greater than zero.'),
                ]);
            }

            if ((float) $vendor->balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => __('Requested amount exceeds vendor balance.'),
                ]);
            }

            return VendorWithdrawal::create([
                'vendor_id' => $vendor->id,
                'amount' => round($amount, 2),
                'status' => 'pending',
                'method' => $method,
                'notes' => $notes,
                'balance_before' => $vendor->balance,
                'balance_after' => $vendor->balance,
            ]);
        });
    }

    public function approve(int $withdrawalId, User $admin): ?VendorWithdrawal
    {
        return DB::transaction(function () use ($withdrawalId, $admin) {
            /** @var VendorWithdrawal|null $withdrawal */
            $withdrawal = VendorWithdrawal::query()
                ->lockForUpdate()
                ->find($withdrawalId);

            if (! $withdrawal || $withdrawal->status !== 'pending') {
                return null;
            }

            $vendor = Vendor::query()->lockForUpdate()->findOrFail((int) $withdrawal->vendor_id);

            $amount = (float) $withdrawal->amount;
            if ((float) $vendor->balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => __('Vendor balance is insufficient to approve this withdrawal.'),
                ]);
            }

            $balanceBefore = (float) $vendor->balance;
            $vendor->balance = round($vendor->balance - $amount, 2);
            $vendor->save();

            $withdrawal->status = 'approved';
            $withdrawal->processed_by = $admin->id;
            $withdrawal->processed_at = now();
            $withdrawal->balance_before = round($balanceBefore, 2);
            $withdrawal->balance_after = $vendor->balance;
            $withdrawal->save();

            VendorBalanceTransaction::create([
                'vendor_id' => $vendor->id,
                'vendor_withdrawal_id' => $withdrawal->id,
                'type' => 'subtraction',
                'amount' => round($amount, 2),
                'balance_after' => $vendor->balance,
                'notes' => 'Withdrawal #'.$withdrawal->id,
                'payload' => [
                    'method' => $withdrawal->method,
                    'processed_by' => $admin->id,
                ],
            ]);

            $withdrawal->refresh();

            $this->notificationService->notifyVendorUsers(
                $vendor->id,
                new VendorWithdrawalStatusUpdatedNotification($withdrawal, 'approved')
            );

            return $withdrawal;
        });
    }

    public function reject(int $withdrawalId, User $admin, ?string $notes = null): ?VendorWithdrawal
    {
        return DB::transaction(function () use ($withdrawalId, $admin, $notes) {
            /** @var VendorWithdrawal|null $withdrawal */
            $withdrawal = VendorWithdrawal::query()
                ->lockForUpdate()
                ->find($withdrawalId);

            if (! $withdrawal || $withdrawal->status !== 'pending') {
                return null;
            }

            $withdrawal->status = 'rejected';
            $withdrawal->processed_by = $admin->id;
            $withdrawal->processed_at = now();
            $withdrawal->notes = $notes ?? $withdrawal->notes;
            $withdrawal->save();

            $this->notificationService->notifyVendorUsers(
                $withdrawal->vendor_id,
                new VendorWithdrawalStatusUpdatedNotification($withdrawal, 'rejected', $notes)
            );

            return $withdrawal;
        });
    }
}
