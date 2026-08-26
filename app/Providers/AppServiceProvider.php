<?php

namespace App\Providers;

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
        // Implicitly grant 'system_administrator' and 'owner' roles full authority across the entire ERP/POS system
        Gate::before(function ($user, $ability) {
            return ($user->hasRole('system_administrator') || $user->hasRole('owner')) ? true : null;
        });
    }
}
