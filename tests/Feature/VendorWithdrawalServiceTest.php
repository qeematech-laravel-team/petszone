<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorWithdrawal;
use App\Notifications\VendorWithdrawalStatusUpdatedNotification;
use App\Services\NotificationService;
use App\Services\VendorWithdrawalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VendorWithdrawalServiceTest extends TestCase
{
    use RefreshDatabase;

    protected VendorWithdrawalService $service;

    protected NotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationService = new NotificationService;
        $this->service = new VendorWithdrawalService($this->notificationService);
    }

    public function test_create_request_for_vendor_success(): void
    {
        $vendor = Vendor::factory()->create(['balance' => 1000.00]);
        $amount = 500.00;

        $withdrawal = $this->service->createRequestForVendor($vendor, $amount, 'bank_transfer', 'Test withdrawal');

        $this->assertDatabaseHas('vendor_withdrawals', [
            'id' => $withdrawal->id,
            'vendor_id' => $vendor->id,
            'amount' => 500.00,
            'status' => 'pending',
            'method' => 'bank_transfer',
            'notes' => 'Test withdrawal',
        ]);

        $this->assertEquals(500.00, $withdrawal->amount);
        $this->assertEquals('pending', $withdrawal->status);
    }

    public function test_create_request_for_vendor_throws_exception_when_amount_is_zero(): void
    {
        $vendor = Vendor::factory()->create(['balance' => 1000.00]);

        $this->expectException(ValidationException::class);

        $this->service->createRequestForVendor($vendor, 0);
    }

    public function test_create_request_for_vendor_throws_exception_when_amount_is_negative(): void
    {
        $vendor = Vendor::factory()->create(['balance' => 1000.00]);

        $this->expectException(ValidationException::class);

        $this->service->createRequestForVendor($vendor, -100);
    }

    public function test_create_request_for_vendor_throws_exception_when_balance_insufficient(): void
    {
        $vendor = Vendor::factory()->create(['balance' => 100.00]);

        $this->expectException(ValidationException::class);

        $this->service->createRequestForVendor($vendor, 500.00);
    }

    public function test_approve_withdrawal_success(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $vendor = Vendor::factory()->create(['balance' => 1000.00]);
        $withdrawal = VendorWithdrawal::factory()->create([
            'vendor_id' => $vendor->id,
            'amount' => 500.00,
            'status' => 'pending',
            'balance_before' => 1000.00,
            'balance_after' => 1000.00,
        ]);

        $result = $this->service->approve($withdrawal->id, $admin);

        $this->assertNotNull($result);
        $this->assertEquals('approved', $result->status);
        $this->assertEquals($admin->id, $result->processed_by);
        $this->assertNotNull($result->processed_at);

        $vendor->refresh();
        $this->assertEquals(500.00, $vendor->balance);

        $this->assertDatabaseHas('vendor_balance_transactions', [
            'vendor_id' => $vendor->id,
            'vendor_withdrawal_id' => $withdrawal->id,
            'type' => 'subtraction',
            'amount' => 500.00,
        ]);

        Notification::assertSentTo(
            $vendor->owner,
            VendorWithdrawalStatusUpdatedNotification::class,
            function ($notification) use ($withdrawal) {
                return $notification->withdrawal->id === $withdrawal->id
                    && $notification->status === 'approved';
            }
        );
    }

    public function test_approve_withdrawal_returns_null_when_not_pending(): void
    {
        $admin = User::factory()->create();
        $vendor = Vendor::factory()->create(['balance' => 1000.00]);
        $withdrawal = VendorWithdrawal::factory()->create([
            'vendor_id' => $vendor->id,
            'status' => 'approved',
        ]);

        $result = $this->service->approve($withdrawal->id, $admin);

        $this->assertNull($result);
    }

    public function test_approve_withdrawal_throws_exception_when_balance_insufficient(): void
    {
        $admin = User::factory()->create();
        $vendor = Vendor::factory()->create(['balance' => 100.00]);
        $withdrawal = VendorWithdrawal::factory()->create([
            'vendor_id' => $vendor->id,
            'amount' => 500.00,
            'status' => 'pending',
        ]);

        $this->expectException(ValidationException::class);

        $this->service->approve($withdrawal->id, $admin);
    }

    public function test_reject_withdrawal_success(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $vendor = Vendor::factory()->create(['balance' => 1000.00]);
        $withdrawal = VendorWithdrawal::factory()->create([
            'vendor_id' => $vendor->id,
            'amount' => 500.00,
            'status' => 'pending',
            'balance_before' => 1000.00,
            'balance_after' => 1000.00,
        ]);

        $rejectionNotes = 'Insufficient documentation';

        $result = $this->service->reject($withdrawal->id, $admin, $rejectionNotes);

        $this->assertNotNull($result);
        $this->assertEquals('rejected', $result->status);
        $this->assertEquals($admin->id, $result->processed_by);
        $this->assertEquals($rejectionNotes, $result->notes);
        $this->assertNotNull($result->processed_at);

        $vendor->refresh();
        $this->assertEquals(1000.00, $vendor->balance);

        Notification::assertSentTo(
            $vendor->owner,
            VendorWithdrawalStatusUpdatedNotification::class,
            function ($notification) use ($withdrawal, $rejectionNotes) {
                return $notification->withdrawal->id === $withdrawal->id
                    && $notification->status === 'rejected'
                    && $notification->notes === $rejectionNotes;
            }
        );
    }

    public function test_reject_withdrawal_returns_null_when_not_pending(): void
    {
        $admin = User::factory()->create();
        $vendor = Vendor::factory()->create(['balance' => 1000.00]);
        $withdrawal = VendorWithdrawal::factory()->create([
            'vendor_id' => $vendor->id,
            'status' => 'approved',
        ]);

        $result = $this->service->reject($withdrawal->id, $admin);

        $this->assertNull($result);
    }

    public function test_reject_withdrawal_without_notes(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $vendor = Vendor::factory()->create(['balance' => 1000.00]);
        $withdrawal = VendorWithdrawal::factory()->create([
            'vendor_id' => $vendor->id,
            'amount' => 500.00,
            'status' => 'pending',
            'notes' => 'Original notes',
        ]);

        $result = $this->service->reject($withdrawal->id, $admin);

        $this->assertNotNull($result);
        $this->assertEquals('rejected', $result->status);
        $this->assertEquals('Original notes', $result->notes);
    }

    public function test_list_for_admin_with_filters(): void
    {
        $vendor1 = Vendor::factory()->create();
        $vendor2 = Vendor::factory()->create();

        VendorWithdrawal::factory()->create([
            'vendor_id' => $vendor1->id,
            'status' => 'pending',
        ]);

        VendorWithdrawal::factory()->create([
            'vendor_id' => $vendor1->id,
            'status' => 'approved',
        ]);

        VendorWithdrawal::factory()->create([
            'vendor_id' => $vendor2->id,
            'status' => 'pending',
        ]);

        $result = $this->service->listForAdmin(15, ['status' => 'pending']);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $withdrawal) {
            $this->assertEquals('pending', $withdrawal->status);
        }

        $result = $this->service->listForAdmin(15, ['vendor_id' => $vendor1->id]);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $withdrawal) {
            $this->assertEquals($vendor1->id, $withdrawal->vendor_id);
        }
    }

    public function test_list_for_vendor(): void
    {
        $vendor1 = Vendor::factory()->create();
        $vendor2 = Vendor::factory()->create();

        VendorWithdrawal::factory()->count(3)->create(['vendor_id' => $vendor1->id]);
        VendorWithdrawal::factory()->count(2)->create(['vendor_id' => $vendor2->id]);

        $result = $this->service->listForVendor($vendor1->id, 15);

        $this->assertCount(3, $result->items());
        foreach ($result->items() as $withdrawal) {
            $this->assertEquals($vendor1->id, $withdrawal->vendor_id);
        }
    }
}
