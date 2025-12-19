<?php

use App\Http\Controllers\SendCallbackMidtrans;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TopupController;
use App\Http\Controllers\TransactionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
Route::post('/logout', [DashboardController::class, 'logout'])->name('logout');

Route::post('/topup', [TopupController::class, 'topup'])->name('topup');

// Transaction routes (API proxy)
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
Route::post('/purchase', [TransactionController::class, 'purchase'])->name('purchase');

Route::post('/mock-callback/success', [SendCallbackMidtrans::class, 'success'])->name('mock-callback.success');
Route::post('/mock-callback/failed', [SendCallbackMidtrans::class, 'failed'])->name('mock-callback.failed');
