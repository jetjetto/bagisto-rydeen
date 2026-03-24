<?php

use Illuminate\Support\Facades\Route;
use Rydeen\Auth\Http\Controllers\LoginController;

Route::middleware('web')->prefix('dealer')->group(function () {
    Route::get('login', [LoginController::class, 'showLogin'])->name('dealer.login');
    Route::post('login', [LoginController::class, 'sendCode'])->name('dealer.login.send-code');
    Route::get('verify', [LoginController::class, 'showVerify'])->name('dealer.verify.form');
    Route::post('verify', [LoginController::class, 'verify'])->name('dealer.verify');
    Route::post('resend-code', [LoginController::class, 'resendCode'])->name('dealer.resend-code');
    Route::post('logout', [LoginController::class, 'logout'])->name('dealer.logout');

    // Placeholder dashboard route — will be replaced by the Dealer package
    Route::get('dashboard', function () {
        return redirect()->route('dealer.login');
    })->name('dealer.dashboard');
});
