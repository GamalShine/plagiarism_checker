<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use App\Services\DokuService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DokuPackageNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'doku.client_id' => 'test-client-id',
            'doku.secret_key' => 'test-secret-key',
        ]);
    }

    public function test_unsigned_success_notification_cannot_unlock_a_package(): void
    {
        $user = User::factory()->create(['pending_package_key' => 'hemat-3']);
        $payment = $this->createPendingPackagePayment($user);

        $response = $this->postJson(route('payment.notification'), [
            'order' => ['invoice_number' => $payment->order_id, 'status' => 'SUCCESS'],
            'transaction' => ['status' => 'SUCCESS'],
        ]);

        $response->assertUnauthorized();
        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('hemat-3', $user->fresh()->pending_package_key);
    }

    public function test_signed_success_notification_grants_package_and_unlocks_routes(): void
    {
        $user = User::factory()->create(['pending_package_key' => 'hemat-3']);
        $payment = $this->createPendingPackagePayment($user);
        $payload = [
            'order' => ['invoice_number' => $payment->order_id, 'status' => 'SUCCESS'],
            'transaction' => ['status' => 'SUCCESS'],
        ];
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $requestId = 'test-request-id';
        $timestamp = '2026-10-03T12:00:00Z';
        $target = '/payment/notification';
        $signature = app(DokuService::class)->generateSignature(
            'test-client-id',
            $requestId,
            $timestamp,
            $target,
            $body,
            'test-secret-key',
        );

        $response = $this->call('POST', $target, [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_CLIENT_ID' => 'test-client-id',
            'HTTP_REQUEST_ID' => $requestId,
            'HTTP_REQUEST_TIMESTAMP' => $timestamp,
            'HTTP_SIGNATURE' => $signature,
        ], $body);

        $response->assertOk();
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertNull($user->fresh()->pending_package_key);
        $this->assertSame('member', $user->fresh()->role);
        $this->assertTrue($user->fresh()->hasActivePackage());

        $this->actingAs($user->fresh())->get(route('user.profile.edit'))->assertOk();
    }

    private function createPendingPackagePayment(User $user): Payment
    {
        return Payment::create([
            'user_id' => $user->id,
            'order_id' => 'PKG-WEBHOOK-' . $user->id,
            'package_key' => 'hemat-3',
            'package_name' => config('plans.hemat-3.name'),
            'package_quota' => config('plans.hemat-3.quota'),
            'package_days' => config('plans.hemat-3.days'),
            'amount' => config('plans.hemat-3.amount'),
            'status' => 'pending',
        ]);
    }
}