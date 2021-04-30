<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Application Policy Mapping.
     * @var array<string,string>
     */
    protected $policies = [
        // empty
    ];

    /**
     * Register any authentication/authorization services.
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('admin', function ($user) {
            if ($user->role === User::ADMIN) {
                return Response::allow();
            } else {
                return Response::deny('The page/action is only allowed for administrators');
            }
        });
    }
}
