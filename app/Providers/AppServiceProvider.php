<?php

namespace App\Providers;

use Illuminate\Routing\Router;
use App\Http\Middleware\JwtMiddleware;
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
    public function boot(Router $router): void
    {
        $router->aliasMiddleware('jwt', JwtMiddleware::class);
    }
}
