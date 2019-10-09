<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //Add this custom validation rule.
        \Validator::extend('alpha_num_spaces', function ($attribute, $value) {
            // This will only accept alpha and spaces.
            // Original from stackoverflow: /^[\pL\s]+$/u
            // If you want to accept hyphens use: /^[\pL\s-]+$/u.
            return preg_match('/^[\pL\s0-9.,-]+$/u', $value); 
        });

        \Validator::extend('alpha_spaces', function ($attribute, $value) {
            // This will only accept alpha and spaces.
            // Original from stackoverflow: /^[\pL\s]+$/u
            // If you want to accept hyphens use: /^[\pL\s-]+$/u.
            return preg_match('/^[\pL\s]+$/u', $value);  
        });
    }
}
