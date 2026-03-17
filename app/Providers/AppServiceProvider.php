<?php

namespace App\Providers;

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
        if(class_exists('\Livewire\Blaze\Blaze')) {
            \Livewire\Blaze\Blaze::optimize()->in(base_path('vendor/juaniquillo/laravel-backend-component/resources/components'));
        }
    }
}
