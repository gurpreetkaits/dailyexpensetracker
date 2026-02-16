<?php

namespace App\Providers;

use App\Mail\Transport\UnosendTransport;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;

class UnosendServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->afterResolving(MailManager::class, function (MailManager $manager) {
            $manager->extend('unosend', function (array $config) {
                return new UnosendTransport(
                    $config['api_key'] ?? config('services.unosend.api_key')
                );
            });
        });
    }
}
