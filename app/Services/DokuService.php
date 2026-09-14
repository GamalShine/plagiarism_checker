<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DokuService
{
    private string $clientId;
    private string $secretKey;
    private bool $isProduction;
    private string $baseUrl;

    public function __construct()
    {
        $this->clientId     = config('doku.client_id', '');
        $this->secretKey    = config('doku.secret_key', '');
        $this->isProduction = (bool) config('doku.is_production', false);
        $this->baseUrl      = $this->isProduction
            ? 'https://api.doku.com'
            : 'https://api-sandbox.doku.com';
    }

    /**
     * Request DOKU Checkout Payment URL (Jokul Checkout)
     */
    public function createPayment(Payment $payment): array
    {
        $targetPath = '/checkout/v1/payment';
        $url = $this->baseUrl . $targetPath;
        $requestId = (string) Str::uuid();
        $requestTimestamp = gmdate('Y-m-d\TH:i:s\Z');
        $publicAppUrl = rtrim((string) config('app.url'), '/');
        $isGuestPayment = filled($payment->guest_token);
        $callbackToken = $isGuestPayment ? $payment->guest_token : $payment->order_id;
        $finishRoute = $isGuestPayment ? 'guest.payment.finish' : 'user.payment.finish';
        $cancelRoute = $isGuestPayment ? 'guest.payment.error' : 'user.payment.error';
        $finishPath = route($finishRoute, $callbackToken, false);
        $cancelPath = route($cancelRoute, $callbackToken, false);

        $body = [
            'order' => [
                'amount'            => (int) $payment->amount,
                'invoice_number'    => $payment->order_id,
                'currency'          => 'IDR',
                'callback_url'      => $publicAppUrl . $finishPath,
                'callback_url_cancel' => $publicAppUrl . $cancelPath,
                'auto_redirect'     => true,
                'line_items'        => [
                    [
                        'name'     => 'Cek Plagiarisme Dokumen',
                        'price'    => (int) $payment->amount,
                        'quantity' => 1,
                    ],
                ],
            ],
            'payment' => [
                'payment_due_date' => 60, // menit
            ],
            'customer' => [
                'id'    => (string) $payment->user_id,
                'name'  => $payment->user->name,
                'email' => $payment->user->email,
            ],
        ];

        $jsonBody = json_encode($body, JSON_UNESCAPED_SLASHES);
        $signature = $this->generateSignature($this->clientId, $requestId, $requestTimestamp, $targetPath, $jsonBody, $this->secretKey);

        try {
            $response = Http::withBody($jsonBody, 'application/json')
                ->withHeaders([
                    'Client-Id'         => $this->clientId,
                    'Request-Id'        => $requestId,
                    'Request-Timestamp' => $requestTimestamp,
                    'Signature'         => $signature,
                ])->post($url);

            $data = $response->json();

            if ($response->successful() && isset($data['response']['payment']['url'])) {
                $paymentUrl = $data['response']['payment']['url'];
                $payment->update([
                    'snap_token' => $paymentUrl, // Simpan payment URL doku
                ]);

                return [
                    'success' => true,
                    'url'     => $paymentUrl,
                    'data'    => $data,
                ];
            }

            Log::error('DOKU Create Payment Failed: ', [
                'status'   => $response->status(),
                'response' => $data,
                'body'     => $body,
            ]);

            return [
                'success' => false,
                'message' => $data['error']['message'] ?? 'Gagal membuat sesi pembayaran DOKU.',
            ];
        } catch (\Exception $e) {
            Log::error('DOKU Create Payment Exception: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate HMAC SHA256 Signature for DOKU Request
     */
    public function generateSignature(
        string $clientId,
        string $requestId,
        string $requestTimestamp,
        string $targetPath,
        string $jsonBody,
        string $secretKey
    ): string {
        $digest = base64_encode(hash('sha256', $jsonBody, true));
        $rawSignature = "Client-Id:" . $clientId . "\n"
            . "Request-Id:" . $requestId . "\n"
            . "Request-Timestamp:" . $requestTimestamp . "\n"
            . "Request-Target:" . $targetPath . "\n"
            . "Digest:" . $digest;

        $signature = base64_encode(hash_hmac('sha256', $rawSignature, $secretKey, true));

        return 'HMACSHA256=' . $signature;
    }

    /**
     * Verify incoming notification signature from DOKU
     */
    public function verifyNotificationSignature(
        string $receivedClientId,
        string $receivedRequestId,
        string $receivedTimestamp,
        string $targetPath,
        string $rawBody,
        string $receivedSignature
    ): bool {
        $expectedSignature = $this->generateSignature(
            $receivedClientId,
            $receivedRequestId,
            $receivedTimestamp,
            $targetPath,
            $rawBody,
            $this->secretKey
        );

        return hash_equals($expectedSignature, $receivedSignature);
    }
}
