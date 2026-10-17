<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Token lifetime comes from config/certihub.php (safe with config:cache).
        config(['sanctum.expiration' => config('certihub.token_ttl')]);

        // Password reset links point to the Vue app, not to a Laravel route.
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return config('certihub.frontend_url')
                .'/reset-password?token='.$token
                .'&email='.urlencode($user->getEmailForPasswordReset());
        });
    }
}
