<?php

use App\Http\Controllers\GuestLinkController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

require __DIR__.'/auth.php';

// Midtrans payment notification webhook – CSRF exempt
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

Route::redirect('/dashboard', '/user/dashboard');
Route::redirect('/plagiarism', '/user/plagiarism');
Route::redirect('/journal', '/user/journal');
Route::redirect('/improvement', '/user/improvement');
Route::redirect('/history', '/user/history');
Route::redirect('/settings', '/user/settings');
Route::redirect('/profile', '/user/profile');

Route::redirect('/admin', '/admin/dashboard');
