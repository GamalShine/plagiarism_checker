<?php

namespace App\Services;

use App\Models\Payment;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use Illuminate\Support\Str;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$clientKey    = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    /**
     * Create a Snap token for a payment.
     */
    public function createSnapToken(Payment $payment): string
    {
        $params = [
            'transaction_details' => [
                'order_id'     => $payment->order_id,
                'gross_amount' => $payment->amount,
            ],
            'customer_details' => [
                'first_name' => $payment->user->name,
                'email'      => $payment->user->email,
            ],
            'item_details' => [
                [
                    'id'       => 'NASKAHCEK-1',
                    'price'    => $payment->amount,
                    'quantity' => 1,
                    'name'     => 'Cek Plagiarisme Dokumen',
                ],
            ],
            'callbacks' => [
                'finish'  => route('user.payment.finish', $payment->order_id),
                'error'   => route('user.payment.error', $payment->order_id),
                'pending' => route('user.payment.pending', $payment->order_id),
            ],
        ];

        $snapToken = Snap::getSnapToken($params);
        $payment->update(['snap_token' => $snapToken]);

        return $snapToken;
    }

    /**
     * Build a unique order ID.
     */
    public function generateOrderId(): string
    {
        return 'PC-' . strtoupper(Str::random(10)) . '-' . time();
    }

    /**
     * Verify and parse an incoming Midtrans notification.
     * Returns the notification object; throws on invalid signature.
     */
    public function parseNotification(): Notification
    {
        $notif = new Notification();

        // Validate signature
        $expectedSig = hash('sha512',
            $notif->order_id .
            $notif->status_code .
            $notif->gross_amount .
            config('midtrans.server_key')
        );

        if ($notif->signature_key !== $expectedSig) {
            throw new \Exception('Invalid Midtrans signature');
        }

        return $notif;
    }

    /**
     * Map Midtrans transaction_status + fraud_status to our simple status.
     */
    public function resolveStatus(Notification $notif): string
    {
        $txStatus    = $notif->transaction_status;
        $fraudStatus = $notif->fraud_status ?? null;

        if ($txStatus === 'capture') {
            return $fraudStatus === 'accept' ? 'paid' : 'failed';
        }

        return match ($txStatus) {
            'settlement' => 'paid',
            'pending'    => 'pending',
            'deny', 'cancel', 'expire' => 'failed',
            default => 'pending',
        };
    }

    public function resolveStatusFromPayload(array $payload): string
    {
        $txStatus    = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        if ($txStatus === 'capture') {
            return $fraudStatus === 'accept' ? 'paid' : 'failed';
        }

        return match ($txStatus) {
            'settlement' => 'paid',
            'pending'    => 'pending',
            'deny', 'cancel', 'expire' => 'failed',
            default => 'pending',
        };
    }
}
