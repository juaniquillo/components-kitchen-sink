<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Blaze\Blaze;

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
        if (class_exists('\Livewire\Blaze\Blaze')) {
            Blaze::optimize()->in(base_path('vendor/juaniquillo/laravel-backend-component/resources/components'));
        }
    }
}
