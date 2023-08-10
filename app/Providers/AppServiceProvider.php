<?php

namespace App\Providers;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Client;
use Ramsey\Uuid\Uuid;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // $this->registerPolicies();
        // Passport::routes();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    protected $policies = [
        'App\Models' => 'App\Policies\ModelPolicy',
    ];

    public function boot()
    {
        Client::creating(function (Client $client) {
            $client->incrementing = false;
            $client->id = \Ramsey\Uuid\Uuid::uuid4()->toString();
        });

        Client::retrieved(function (Client $client) {
            $client->incrementing = false;
        });
    }

}
