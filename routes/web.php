<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BenefitDeliveryController;
use App\Http\Controllers\DashboardController;

Route::get('/', fn() => redirect()->route('dashboard'))->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::patch('/benefit-deliveries/{benefitDelivery}/deliver', [BenefitDeliveryController::class, 'deliver'])->name('benefit-deliveries.deliver');
    Route::get('/benefit-deliveries/filter', [BenefitDeliveryController::class, 'filter'])->name('benefit-deliveries.filter');
    Route::patch('/benefit-deliveries/quick-deliver', [BenefitDeliveryController::class, 'quickDeliver'])->name('benefit-deliveries.quick-deliver');
    Route::resource('/benefit-deliveries', BenefitDeliveryController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

require __DIR__.'/auth.php';
