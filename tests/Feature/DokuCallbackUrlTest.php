<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use App\Services\DokuService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DokuCallbackUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_checkout_callback_uses_the_configured_base_url(): void
    {
        config([
            'app.url' => 'http://plagiarism_checker.test',
            'doku.callback_base_url' => 'http://127.0.0.1/plagiarism_checker/public',
            'doku.client_id' => 'test-client',
            'doku.secret_key' => 'test-secret',
            'doku.is_production' => false,
        ]);

        Http::fake([
            'https://api-sandbox.doku.com/checkout/v1/payment' => Http::response([
                'response' => ['payment' => ['url' => 'https://pay.doku.com/test-checkout']],
            ]),
        ]);

        $user = User::factory()->create();
        $token = 'guest-return-token';
        $payment = Payment::create([
            'user_id' => $user->id,
            'order_id' => 'GPC-CALLBACK-TEST',
            'guest_token' => $token,
            'amount' => 10000,
            'status' => 'pending',
        ]);

        $result = app(DokuService::class)->createPayment($payment);

        $this->assertTrue($result['success']);
        Http::assertSent(function ($request) use ($payment, $token): bool {
            $payload = $request->data();

            return $payload['order']['callback_url'] === "http://127.0.0.1/plagiarism_checker/public/guest-payment/{$token}/finish"
                && $payload['order']['callback_url_cancel'] === "http://127.0.0.1/plagiarism_checker/public/guest-payment/{$token}/error"
                && $payload['order']['invoice_number'] === $payment->order_id;
        });
    }

    public function test_inactive_package_user_is_charged_configured_single_check_price(): void
    {
        config([
            'doku.client_id' => 'test-client',
            'doku.secret_key' => 'test-secret',
            'doku.is_production' => false,
            'doku.single_check_price' => 8000,
        ]);
        Http::fake([
            'https://api-sandbox.doku.com/checkout/v1/payment' => Http::response([
                'response' => ['payment' => ['url' => 'https://pay.doku.com/test-checkout']],
            ]),
        ]);
        Storage::fake('local');

        $user = User::factory()->create([
            'role' => 'user',
            'package_key' => 'hemat-3',
            'package_credits' => 0,
            'package_expires_at' => now()->addDays(2),
        ]);
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);

        $response = $this->actingAs($user)->post(route('user.plagiarism.pay'), [
            'file' => UploadedFile::fake()->createWithContent('draft.txt', str_repeat('Sample document content. ', 10)),
            'sources' => ['web'],
        ]);

        $response->assertRedirect('https://pay.doku.com/test-checkout');
        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'amount' => 8000,
            'status' => 'pending',
        ]);
    }
}