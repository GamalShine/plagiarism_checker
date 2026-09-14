<?php

namespace App\Http\Middleware;

use App\Models\Payment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePackagePaymentCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $request->routeIs('user.payment.*')) {
            return $next($request);
        }

        $pendingPayment = Payment::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereNotNull('package_key')
            ->latest('id')
            ->first();

        $packageKey = $pendingPayment?->package_key ?: $user->pending_package_key;

        if ($packageKey) {
            return redirect()
            ->route('user.payment.package', $packageKey)
                ->with('payment_required_alert', true);
        }

        return $next($request);
    }
}
