<?php

namespace App\Providers;

use App\Http\View\Composers\MenuComposer;
use App\View\Composers\NavigationComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the menu composer for the app layout
        View::composer('layouts.app', MenuComposer::class);
        
    }
}