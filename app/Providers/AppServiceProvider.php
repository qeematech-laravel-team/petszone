<?php

namespace App\Providers;

use App\Models\VendorUser;
use App\Models\Verification;
use App\View\Composers\SidebarComposer;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
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
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $code = rand(100000, 999999);

            Verification::create([
                'user_id' => $notifiable->id,
                'type' => 'email',
                'target' => $notifiable->email,
                'code' => $code,
                'expires_at' => now()->addMinutes(10),
            ]);

            return $code;
        });

        VerifyEmail::toMailUsing(function ($notifiable, $code) {
            return (new MailMessage)
                ->subject('Email Verification Code')
                ->view('emails.verify-code', [
                    'code' => $code,
                    'user' => $notifiable,
                ]);
        });

        // Share vendor user data only with the views that need it (wildcard '*' would run on every rendered view/partial)
        View::composer(['layouts.partials.sidebar', 'vendor.*'], SidebarComposer::class);

        // Configure route model binding for vendor-users (exclude soft deleted)
        Route::bind('vendor_user', function ($value) {
            return VendorUser::where('id', $value)->firstOrFail();
        });
    }
}
