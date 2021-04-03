<?php

namespace App\Providers;

use App\Models\ApiToken;
use App\Models\Order;
use App\Models\Pet;
use App\Models\User;
use App\Policies\OrderPolicy;
use App\Policies\PetPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
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
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->input('api_token')) {
                $apiToken = ApiToken::where('value', $request->input('api_token'))
                    ->valid()
                    ->first();

                if ($apiToken) {
                    return $apiToken->user;
                }
            }
        });

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Pet::class, PetPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);

        // Gate::define('view-account', function ($user, $account) {
        //     return $user->id === $account->id;
        // });
    }
}
