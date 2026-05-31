<?php

namespace App\Providers;

use App\Models\Path;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $paths = Path::where('is_active', true)
                ->orderBy('sort_order')
                ->with('subjects')
                ->get();
            $view->with('navPaths', $paths);
        });
    }
}
