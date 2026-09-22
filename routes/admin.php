<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LinkController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PlagiarismController;
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
            Route::get('/document/{plagiarismCheck}', [PlagiarismController::class, 'documentPdf'])->name('document');
            Route::get('/export/{plagiarismCheck}', [PlagiarismController::class, 'export'])->name('export');
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
