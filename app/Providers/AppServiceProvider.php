<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Pagination memakai gaya Bootstrap 5 (bukan Tailwind bawaan Laravel).
        Paginator::useBootstrapFive();
    }
}
