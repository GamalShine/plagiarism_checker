<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LinkController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ImprovementController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\PlagiarismController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('plagiarism')->name('plagiarism.')->group(function () {
            Route::get('/', [PlagiarismController::class, 'index'])->name('index');
            Route::post('/extract-chapters', [PlagiarismController::class, 'extractChapters'])->name('extract_chapters');
            Route::post('/check', [PlagiarismController::class, 'check'])->name('check');
            Route::post('/pay', [PlagiarismController::class, 'check'])->name('pay'); // admin skip payment
            Route::get('/result/{plagiarismCheck}', [PlagiarismController::class, 'result'])->name('result');
            Route::get('/result/{plagiarismCheck}/status', [PlagiarismController::class, 'status'])->name('status');
            Route::patch('/result/{plagiarismCheck}/similarity', [PlagiarismController::class, 'updateSimilarity'])->name('update_similarity');
            Route::get('/document-docx/{plagiarismCheck}', [PlagiarismController::class, 'documentDocx'])->name('document_docx');
            Route::get('/document/{plagiarismCheck}', [PlagiarismController::class, 'documentPdf'])->name('document');
            Route::get('/export/{plagiarismCheck}', [PlagiarismController::class, 'export'])->name('export');
        });

        Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
        Route::delete('/history/bulk-delete', [HistoryController::class, 'destroyBulk'])->name('history.destroyBulk');

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

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/', [SettingController::class, 'update'])->name('update');
            Route::post('/dark-mode', [SettingController::class, 'toggleDarkMode'])->name('dark-mode');
        });

        Route::prefix('links')->name('links.')->group(function () {
            Route::get('/', [LinkController::class, 'index'])->name('index');
            Route::post('/', [LinkController::class, 'store'])->name('store');
            Route::put('/{link}', [LinkController::class, 'update'])->name('update');
            Route::delete('/{link}', [LinkController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });
    });
