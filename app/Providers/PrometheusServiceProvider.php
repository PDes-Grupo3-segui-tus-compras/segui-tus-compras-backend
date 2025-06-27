<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Prometheus\Storage\Redis;
use Prometheus\CollectorRegistry;

class PrometheusServiceProvider extends ServiceProvider {
    public function register() {
        $this->app->singleton(CollectorRegistry::class, function () {
            $redisAdapter = new Redis([
                'host' => env('PROMETHEUS_REDIS_HOST', 'redis'),
                'port' => 6379,
                'timeout' => 0.1,
                'read_timeout' => 10,
                'persistent_connections' => false,
            ]);

            return new CollectorRegistry($redisAdapter);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
