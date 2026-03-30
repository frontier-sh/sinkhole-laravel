<?php

namespace Frontier\Sinkhole;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class SinkholeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Mail::extend('sinkhole', function (array $config) {
            return new SinkholeTransport(
                endpoint: $config['endpoint'],
                apiKey: $config['api_key'],
                channel: $config['channel'] ?? 'default',
            );
        });
    }
}
