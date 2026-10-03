<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackagePaymentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_unpaid_package_is_redirected_to_checkout_after_login(): void
    {
        $user = User::factory()->create(['pending_package_key' => 'hemat-3']);
        $this->createPendingPackagePayment($user);

        $this->actingAs($user)->post('/logout')->assertRedirect('/');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('user.payment.package', 'hemat-3', absolute: false));
    }

    public function test_user_with_unpaid_package_cannot_open_other_account_pages(): void
    {
        $user = User::factory()->create(['pending_package_key' => 'hemat-3']);
        $this->createPendingPackagePayment($user);

        $response = $this->actingAs($user)->get(route('user.dashboard'));

        $response->assertRedirect(route('user.payment.package', 'hemat-3'));
    }

    public function test_returning_from_doku_does_not_mark_an_unconfirmed_package_payment_paid(): void
    {
        $user = User::factory()->create(['pending_package_key' => 'hemat-3']);
        $payment = $this->createPendingPackagePayment($user);

        $response = $this->actingAs($user)->get(route('user.payment.package.finish', $payment->order_id));

        $response->assertRedirect(route('user.payment.package', 'hemat-3'));
        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('hemat-3', $user->fresh()->pending_package_key);
    }

    private function createPendingPackagePayment(User $user): Payment
    {
        return Payment::create([
            'user_id' => $user->id,
            'order_id' => 'PKG-TEST-' . $user->id,
            'package_key' => 'hemat-3',
            'package_name' => config('plans.hemat-3.name'),
            'package_quota' => config('plans.hemat-3.quota'),
            'package_days' => config('plans.hemat-3.days'),
            'amount' => config('plans.hemat-3.amount'),
            'status' => 'pending',
        ]);
    }
}