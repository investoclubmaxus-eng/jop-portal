<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

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
        // 🔑 Override the default password reset URL to point to your frontend client
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $frontendUrl = "http://localhost:3000/admin/reset-password"; // Change to your frontend URL
            
            return "{$frontendUrl}?token={$token}&email=" . urlencode($notifiable->getEmailForPasswordReset());
        });
    }
}
