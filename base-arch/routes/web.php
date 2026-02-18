<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FinanceAnalyticsController;
use App\Http\Controllers\OrderAnalyticsController;
use App\Http\Middleware\JwtAuthenticate;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/registrar', [AuthController::class, 'showRegister'])->name('register');

Route::post('/registrar', [AuthController::class, 'register'])->name('register.submit');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware([JwtAuthenticate::class])->group(function () {
    Route::get('/bem-vindo', [AuthController::class, 'welcome'])->name('welcome');
    Route::get('/relatorios/pedidos-complexo', [OrderAnalyticsController::class, 'complexReport'])->name('reports.orders.complex');
    Route::get('/relatorios/financeiro/carteiras', [FinanceAnalyticsController::class, 'portfolioRiskReport'])->name('reports.finance.portfolios');
});
