<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
         $this->app->singleton(AttendanceService::class, function ($app) {
        return new AttendanceService();
    });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Default API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // File upload rate limiting - more restrictive for file operations
        RateLimiter::for('uploads', function (Request $request) {
            return [
                // 10 uploads per minute per user
                Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()),
                // 50 uploads per hour per user to prevent abuse
                Limit::perHour(50)->by($request->user()?->id ?: $request->ip()),
            ];
        });
    }
}
