<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define gates for authorization
        Gate::define('manage-tasks', function ($user) {
            return in_array($user->role, ['admin', 'user']);
        });

        Gate::define('manage-projects', function ($user) {
            return in_array($user->role, ['admin', 'user']);
        });

        Gate::define('manage-users', function ($user) {
            return $user->role === 'admin';
        });
    }
}
