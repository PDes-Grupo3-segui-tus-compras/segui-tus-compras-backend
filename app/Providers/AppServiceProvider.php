<?php

namespace App\Providers;

use App\Contracts\AuthServiceInterface;
use App\Contracts\OpinionRepositoryInterface;
use App\Contracts\ProductRepositoryInterface;
use App\Repositories\OpinionRepository;
use App\Repositories\ProductRepository;
use App\Services\AuthService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(OpinionRepositoryInterface::class, OpinionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
