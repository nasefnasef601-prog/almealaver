<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Path;
use App\Models\Quiz;
use App\Models\User;
use App\Policies\CoursePolicy;
use App\Policies\QuizPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Quiz::class, QuizPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        View::composer('layouts.app', function ($view) {
            $paths = Path::where('is_active', true)
                ->orderBy('sort_order')
                ->with('subjects')
                ->get();
            $view->with('navPaths', $paths);
        });
    }
}
