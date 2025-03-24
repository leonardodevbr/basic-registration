<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if ($user && $user->hasRole('SuperAdmin')) {
                return true; // Ignora todas as permissões, libera tudo
            }

            return null; // Continua fluxo normal
        });

        Blade::if('superadmin', function () {
            return auth()->check() && auth()->user()->hasRole('SuperAdmin');
        });
    }
}
