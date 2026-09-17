<?php

use App\Http\Controllers\FreeCheckController;
use App\Http\Controllers\GuestLinkController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WelcomeController;
use App\Support\HomeRoute;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::view('/harga', 'pricing')->name('pricing');
Route::get('/cekplagiasiturnitin', [FreeCheckController::class, 'index'])->name('free.check.index');
Route::post('/cekplagiasiturnitin/extract-chapters', [FreeCheckController::class, 'extractChapters'])->name('free.plagiarism.extract_chapters');
Route::post('/cekplagiasiturnitin/plagiarism', [FreeCheckController::class, 'guestFileCheck'])->name('free.plagiarism.check');
Route::get('/guest-payment/{token}/finish', [PaymentController::class, 'guestFinish'])->name('guest.payment.finish');
Route::get('/guest-payment/{token}', [PaymentController::class, 'guestWaiting'])->name('guest.payment.waiting');
Route::get('/guest-payment/{token}/status', [PaymentController::class, 'guestStatus'])->name('guest.payment.status');
Route::get('/guest-payment/{token}/result', [PaymentController::class, 'guestResult'])->name('guest.payment.result');
Route::get('/guest-payment/{token}/export', [PaymentController::class, 'guestExport'])->name('guest.payment.export');
Route::get('/guest-payment/{token}/error', [PaymentController::class, 'guestError'])->name('guest.payment.error');
Route::post('/cekplagiasiturnitin/check', [FreeCheckController::class, 'check'])->name('free.check.process');
Route::get('/cekplagiasiturnitin/status/{plagiarismCheck}', [FreeCheckController::class, 'status'])->name('free.check.status');
Route::get('/cekplagiasiturnitin/export/{plagiarismCheck}', [FreeCheckController::class, 'export'])->name('free.check.export');
Route::post('/cekplagiasiturnitin/fetch-url', [FreeCheckController::class, 'fetchUrl'])->name('free.check.fetch_url');

Route::get('/template-jurnal', function () {
    return view('templates.index');
})->name('templates.index');

require __DIR__.'/auth.php';

// Midtrans payment notification webhook – CSRF exempt
Route::get('/payment/notification', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Endpoint webhook aktif. DOKU harus mengirim notifikasi menggunakan POST.',
    ]);
})->name('payment.notification.health');

Route::get('/internal/cron/plagiarism/{token}', function (string $token) {
    $configuredToken = (string) config('app.cron_secret');

    abort_if($configuredToken === '' || ! hash_equals($configuredToken, $token), 404);

    set_time_limit(300);
    $exitCode = Artisan::call('queue:work', [
        'connection' => 'database',
        '--queue' => 'plagiarism',
        '--once' => true,
        '--tries' => 1,
        '--timeout' => 300,
        '--no-interaction' => true,
    ]);

    return response()->json([
        'success' => $exitCode === 0,
        'exit_code' => $exitCode,
        'output' => trim(Artisan::output()),
    ], $exitCode === 0 ? 200 : 500);
})->name('internal.cron.plagiarism');

Route::post('/payment/notification', [PaymentController::class, 'notification'])
    ->name('payment.notification')
    ->withoutMiddleware(['web']);

Route::prefix('cek')->name('guest.link.')->group(function () {
    Route::get('/{token}', [GuestLinkController::class, 'show'])->name('show');
    Route::post('/{token}/check', [GuestLinkController::class, 'check'])->name('check');
    Route::get('/{token}/hasil/{plagiarismCheckId}', [GuestLinkController::class, 'result'])->name('result');
});

require __DIR__.'/user.php';
require __DIR__.'/admin.php';

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('user.dashboard');
    });

    Route::get('/plagiarism', function () {
        if (auth()->user()?->isAdmin()) {
            return redirect()->route('admin.plagiarism.index');
        }

        return redirect()->route('user.plagiarism.index');
    });

    Route::get('/journal', function () {
        if (auth()->user()?->isAdmin()) {
            return redirect()->route('admin.journal.index');
        }

        return redirect()->route('user.journal.index');
    });

    Route::get('/improvement', function () {
        if (auth()->user()?->isAdmin()) {
            return redirect()->route('admin.improvement.index');
        }

        return redirect()->route('user.improvement.index');
    });

    Route::get('/history', function () {
        if (auth()->user()?->isAdmin()) {
            return redirect()->route('admin.history.index');
        }

        return redirect()->route('user.history.index');
    });

    Route::get('/settings', function () {
        if (auth()->user()?->isAdmin()) {
            return redirect()->route('admin.settings.index');
        }

        return redirect()->route('user.settings.index');
    });

    Route::get('/profile', function () {
        if (auth()->user()?->isAdmin()) {
            return redirect()->route('admin.users.index');
        }

        return redirect()->route('user.profile.edit');
    });
});

Route::redirect('/admin', '/admin/dashboard');
