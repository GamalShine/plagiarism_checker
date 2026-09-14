<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ImprovementController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlagiarismController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('plagiarism')->name('plagiarism.')->group(function () {
            Route::get('/', [PlagiarismController::class, 'index'])->name('index');
            Route::post('/extract-chapters', [PlagiarismController::class, 'extractChapters'])->name('extract_chapters');
            Route::post('/check', [PlagiarismController::class, 'check'])->name('check');
            Route::post('/pay', [PaymentController::class, 'initiate'])->name('pay');
            Route::get('/result/{plagiarismCheck}', [PlagiarismController::class, 'result'])->name('result');
            Route::get('/result/{plagiarismCheck}/status', [PlagiarismController::class, 'status'])->name('status');
            Route::get('/document-docx/{plagiarismCheck}', [PlagiarismController::class, 'documentDocx'])->name('document_docx');
            Route::get('/document/{plagiarismCheck}', [PlagiarismController::class, 'documentPdf'])->name('document');
            Route::get('/export/{plagiarismCheck}', [PlagiarismController::class, 'export'])->name('export');
        });

        Route::prefix('payment')->name('payment.')->group(function () {
            Route::post('/{orderId}/confirm', [PaymentController::class, 'confirm'])->name('confirm');
            Route::get('/{orderId}/confirm', function (string $orderId) {
                return redirect()->route('user.payment.finish', $orderId);
            });
            Route::get('/{orderId}/status', [PaymentController::class, 'status'])->name('status');
            Route::get('/{orderId}/finish', [PaymentController::class, 'finish'])->name('finish');
            Route::get('/{orderId}/error', [PaymentController::class, 'error'])->name('error');
            Route::get('/{orderId}/pending', [PaymentController::class, 'pending'])->name('pending');
        });

        Route::prefix('journal')->name('journal.')->group(function () {
            Route::get('/', [JournalController::class, 'index'])->name('index');
            Route::get('/create', [JournalController::class, 'create'])->name('create');
            Route::post('/generate', [JournalController::class, 'generate'])->name('generate');
            Route::get('/{journal}', [JournalController::class, 'show'])->name('show');
            Route::get('/download/{journal}/{type?}', [JournalController::class, 'download'])->name('download');
        });

        Route::prefix('improvement')->name('improvement.')->group(function () {
            Route::get('/', [ImprovementController::class, 'index'])->name('index');
            Route::post('/analyze', [ImprovementController::class, 'analyze'])->name('analyze');
            Route::get('/{improvement}', [ImprovementController::class, 'show'])->name('show');
            Route::post('/{improvement}/apply', [ImprovementController::class, 'apply'])->name('apply');
            Route::get('/{improvement}/download', [ImprovementController::class, 'download'])->name('download');
        });

        Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
        Route::delete('/history/bulk-delete', [HistoryController::class, 'destroyBulk'])->name('history.destroyBulk');

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/', [SettingController::class, 'update'])->name('update');
            Route::post('/dark-mode', [SettingController::class, 'toggleDarkMode'])->name('dark-mode');
        });

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
