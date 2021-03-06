<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $this->customizeVerificationEmail();
        $this->customizeResetPasswordUrl();
    }

    /**
     * Customize verification email.
     *
     * @return void
     */
    private function customizeVerificationEmail()
    {
        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            // Verification URL of frontend
            $url = config('frontend.url')
                . '/auth/verify-email?url=' . urlencode($url);

            return (new MailMessage)
                ->subject('Verify Email Address')
                ->line('Please click the button below to verify your email address.')
                ->action('Verify Email Address', $url)
                ->line('If you did not create an account, no further action is required.');
        });
    }

    /**
     * Customize reset password URL.
     *
     * @return void
     */
    public function customizeResetPasswordUrl()
    {
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return config('frontend.url')
                . '/auth/reset-password?token='. $token
                . '&email=' . urlencode($user->email);
        });
    }
}
