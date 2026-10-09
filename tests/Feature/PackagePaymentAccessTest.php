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

    public function test_user_with_active_package_uses_direct_check_route_even_if_role_is_user(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'package_key' => 'hemat-3',
            'package_credits' => 3,
            'package_expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($user)->get(route('user.plagiarism.index'));

        $response->assertOk();
        $response->assertSee('action="' . route('user.plagiarism.check') . '"', false);
        $response->assertDontSee('action="' . route('user.plagiarism.pay') . '"', false);
    }

    public function test_active_package_user_is_not_redirected_by_a_stale_pending_package_payment(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'package_key' => 'hemat-3',
            'package_credits' => 3,
            'package_expires_at' => now()->addDays(7),
            'pending_package_key' => 'praktis-10',
        ]);
        $this->createPendingPackagePayment($user);

        $response = $this->actingAs($user)->get(route('user.plagiarism.index'));

        $response->assertOk();
        $response->assertSee('action="' . route('user.plagiarism.check') . '"', false);
    }

    public function test_user_with_exhausted_package_sees_single_check_confirmation_details(): void
    {
        config(['doku.single_check_price' => 8000]);
        $user = User::factory()->create([
            'role' => 'user',
            'package_key' => 'hemat-3',
            'package_credits' => 0,
            'package_expires_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($user)->get(route('user.plagiarism.index'));

        $response->assertOk();
        $response->assertSee('data-package-inactive="true"', false);
        $response->assertSee('data-package-inactive-message="Kuota cek plagiarisme paket Anda sudah habis."', false);
        $response->assertSee('data-single-check-price="8000"', false);
        $response->assertSee('showCancelButton: true', false);
    }

    public function test_package_checkout_shows_the_configured_price_for_each_plan(): void
    {
        config([
            'plans.hemat-3.amount' => 20000,
            'plans.praktis-10.amount' => 60000,
            'plans.pro-30.amount' => 115000,
            'plans.ultimato-100.amount' => 250000,
        ]);
        $user = User::factory()->create();
        $prices = [
            'hemat-3' => 20000,
            'praktis-10' => 60000,
            'pro-30' => 115000,
            'ultimato-100' => 250000,
        ];

        foreach ($prices as $packageKey => $price) {
            $response = $this->actingAs($user)->get(route('user.payment.package', $packageKey));

            $response->assertOk();
            $response->assertSee('Rp ' . number_format($price, 0, ',', '.'));
        }
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