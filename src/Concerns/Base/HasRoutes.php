<?php

namespace Rotaz\FilamentAccounts\Concerns\Base;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Rotaz\FilamentAccounts\Http\Controllers\AccountInvitationController;
use Rotaz\FilamentAccounts\Http\Controllers\OAuthController;
use Rotaz\FilamentAccounts\Mail\AccountInvitation;
use Rotaz\FilamentAccounts\Pages\Auth\PrivacyPolicy;
use Rotaz\FilamentAccounts\Pages\Auth\Terms;


trait HasRoutes
{

    public static bool $registersRoutes = true;

    public function ignoreRoutes(): static
    {
        static::$registersRoutes = false;

        return $this;
    }

    protected function registerPublicRoutes(): void
    {
        if (static::hasSocialiteFeatures()) {
            Route::get('/oauth/{provider}', [OAuthController::class, 'redirectToProvider'])->name('oauth.redirect');
            Route::get('/oauth/{provider}/callback', [OAuthController::class, 'handleProviderCallback'])->name('oauth.callback');
        }

        if (static::hasTermsAndPrivacyPolicyFeature()) {
            Route::get(Terms::getSlug(), Terms::class)->name(Terms::getRouteName());
            Route::get(PrivacyPolicy::getSlug(), PrivacyPolicy::class)->name(PrivacyPolicy::getRouteName());
        }
    }

    protected function registerAuthenticatedRoutes(): void
    {
        if (static::sendsAccountInvitations()) {
            Route::get('/invitations/{invitation}', [AccountInvitationController::class, 'accept'])
                ->middleware(['signed'])
                ->name('invitations.accept');
        }
    }

    public static function route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return route(static::generateRouteName($name), $parameters, $absolute);
    }

    public static function generateRouteName(string $name): string
    {
        return 'filament.' . static::getAccountPanel() . ".{$name}";
    }

    public static function generateOAuthRedirectUrl(string $provider): string
    {
        return static::route('oauth.redirect', compact('provider'));
    }

    public static function generateAcceptInvitationUrl(AccountInvitation $invitation): string
    {
        return URL::signedRoute(static::generateRouteName('invitations.accept'), compact('invitation'));
    }
}
