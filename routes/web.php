<?php

use App\Http\Controllers\AccessControlController;
use App\Http\Controllers\BenefitDeliveryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

Route::get('/', fn() => redirect()->route('login'));

Route::get('logs', [LogViewerController::class, 'index'])
    ->middleware(['auth', 'role:SuperAdmin']);

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:view dashboard');

    Route::patch('/benefit-deliveries/{benefitDelivery}/deliver', [BenefitDeliveryController::class, 'deliver'])
        ->name('benefit-deliveries.deliver')
        ->middleware('permission:update benefit deliveries');

    Route::get('/benefit-deliveries/filter', [BenefitDeliveryController::class, 'filter'])
        ->name('benefit-deliveries.filter')
        ->middleware('permission:view benefit deliveries');

    Route::patch('/benefit-deliveries/quick-deliver', [BenefitDeliveryController::class, 'quickDeliver'])
        ->name('benefit-deliveries.quick-deliver')
        ->middleware('permission:update benefit deliveries');

    Route::post('/benefit-deliveries/{id}/reissue', [BenefitDeliveryController::class, 'reissue'])
        ->name('benefit-deliveries.reissue')
        ->middleware('permission:update benefit deliveries');

    Route::get('/benefit-deliveries/{id}/receipt', [BenefitDeliveryController::class, 'generateReceipt'])
        ->name('benefit-deliveries.receipt')
        ->middleware('permission:view benefit deliveries');

    Route::resource('/benefit-deliveries', BenefitDeliveryController::class)
        ->middleware('permission:view benefit deliveries');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit')
        ->middleware('permission:view own profile');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update')
        ->middleware('permission:update own profile');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy')
        ->middleware('permission:update own profile');

    Route::group(['prefix' => 'api'], function ($route) {
        $route->get('/buscar-pessoa', [PersonController::class, 'buscar'])->name('api.buscar-pessoa');
    });
});

Route::middleware(['auth', 'permission:manage roles and permissions'])->group(function () {
    Route::group(['prefix' => 'access-control'], function () {
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
        Route::resource('users', UserController::class);
        Route::get('users/{user}/permissions', [UserPermissionController::class, 'edit'])->name(
            'users.permissions.edit'
        );
        Route::patch('users/{user}/permissions', [UserPermissionController::class, 'update'])->name(
            'users.permissions.update'
        );

        Route::get('/roles', [AccessControlController::class, 'index'])->name('access-control.roles');
        Route::get('/permissions', [AccessControlController::class, 'index'])->name('access-control.permissions');
        Route::get('/users', [AccessControlController::class, 'index'])->name('access-control.users');
    });
});

require __DIR__.'/auth.php';
