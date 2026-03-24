<?php

use Illuminate\Support\Facades\Route;
use Rydeen\Pricing\Http\Controllers\Admin\PromotionController;

Route::group(['middleware' => ['admin'], 'prefix' => config('app.admin_url')], function () {
    Route::prefix('rydeen/promotions')->group(function () {
        Route::get('/', [PromotionController::class, 'index'])->name('admin.rydeen.promotions.index');
        Route::get('/create', [PromotionController::class, 'create'])->name('admin.rydeen.promotions.create');
        Route::post('/', [PromotionController::class, 'store'])->name('admin.rydeen.promotions.store');
        Route::get('/{id}/edit', [PromotionController::class, 'edit'])->name('admin.rydeen.promotions.edit');
        Route::put('/{id}', [PromotionController::class, 'update'])->name('admin.rydeen.promotions.update');
        Route::delete('/{id}', [PromotionController::class, 'destroy'])->name('admin.rydeen.promotions.destroy');
    });
});
