<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
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
    public function boot(): void
    {
        // Required for older MySQL/MariaDB setups (common on cPanel)
        // where utf8mb4 indexed columns can exceed max key length.
        Schema::defaultStringLength(191);

        $this->configureRateLimiting();
    }

    /**
     * Laravel's `api` middleware group ships with no rate limiter at all, so
     * every credential-facing route has to name one.
     *
     * These are keyed on the client address alone. The stock `throttle:n,m`
     * middleware keys on the authenticated user id whenever a session exists
     * (ThrottleRequests::resolveRequestSignature), which is exactly wrong here:
     * a successful registration logs the caller in as a brand new user, so the
     * next attempt would land in a fresh bucket and the limit would never bite.
     */
    private function configureRateLimiting(): void
    {
        // Login and registration: the endpoints worth grinding.
        RateLimiter::for('auth', fn(Request $request) => Limit::perMinute(5)->by($request->ip()));

        // Mail-triggering endpoints — slower still, because each attempt costs
        // us a message and costs the address owner an inbox.
        RateLimiter::for('auth-mail', fn(Request $request) => Limit::perMinute(3)->by($request->ip()));
    }
}
