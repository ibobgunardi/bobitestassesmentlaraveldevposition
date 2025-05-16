<?php

namespace App\Providers;
use Illuminate\Support\Facades\URL;

use App\Models\Project;
use App\Http\View\Composers\MenuComposer;
use Illuminate\Support\Facades\View;
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
        // Share projects data with the app layout
        View::composer('layouts.app', function ($view) {
            $view->with([
                'projects' => Project::orderBy('name')->get(),
                'searchQuery' => request('search'),
                'selectedProjectId' => request('project_id')
            ]);
        });
        // Auto-detect HTTPS (works for most cases)
        URL::forceScheme(request()->getScheme());

        // Or for more control:
        URL::forceScheme(app()->environment('local') ? 'http' : 'https');
        // Register the menu composer
        View::composer('layouts.app', MenuComposer::class);
    }
}
