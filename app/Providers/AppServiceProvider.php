<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
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
        //
        if ($this->app->environment('local')) {
            // 4 - ru sh
            Auth::loginUsingId(1); // Automatically log in the user with ID 1 for local environment

        }
    }
}
