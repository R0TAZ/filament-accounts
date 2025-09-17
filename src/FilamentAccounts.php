<?php

namespace Rotaz\FilamentAccounts;

use Filament\Contracts\Plugin;
use Filament\Events\TenantSet;
use Livewire\Livewire;
use Illuminate\Support\Facades\Event;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;
use Rotaz\FilamentAccounts\Contracts\CreatesConnectedAccounts;
use Rotaz\FilamentAccounts\Contracts\CreatesUserFromProvider;
use Rotaz\FilamentAccounts\Contracts\HandlesInvalidState;
use Rotaz\FilamentAccounts\Contracts\UpdatesConnectedAccounts;
use Rotaz\FilamentAccounts\Http\Controllers\OAuthController;
use Rotaz\FilamentAccounts\Http\Responses\Auth\FilamentAccountsRegistrationResponse;
use Rotaz\FilamentAccounts\Listeners\SwitchCurrentAccount;
use Rotaz\FilamentAccounts\Pages\Company\AccountSettings;
use Rotaz\FilamentAccounts\Pages\Company\CreateAccount;
use Illuminate\Contracts\Foundation\Application;
class FilamentAccounts implements Plugin
{
    use Concerns\Base\HasAddedProfileComponents;
    use Concerns\Base\HasAutoAcceptInvitations;
    use Concerns\Base\HasBaseActionBindings;
    use Concerns\Base\HasBaseModels;
    use Concerns\Base\HasBaseProfileComponents;
    use Concerns\Base\HasBaseProfileFeatures;
    use Concerns\Base\HasAccountFeatures;
    use Concerns\Base\HasModals;
    use Concerns\Base\HasNotifications;
    use Concerns\Base\HasPanels;
    use Concerns\Base\HasPermissions;
    use Concerns\Base\HasRoutes;
    use Concerns\Base\HasTermsAndPrivacyPolicy;
    use Concerns\ManagesProfileComponents;
    use Concerns\Socialite\CanEnableSocialite;
    use Concerns\Socialite\HasConnectedAccountModel;
    use Concerns\Socialite\HasProviderFeatures;
    use Concerns\Socialite\HasProviders;
    use Concerns\Socialite\HasSocialiteActionBindings;
    use Concerns\Socialite\HasSocialiteComponents;
    use Concerns\Socialite\HasSocialiteProfileFeatures;

    public function getId(): string
    {
        return 'accounts';
    }
    public static function make(): static
    {
        return app(static::class);
    }
    public function register(\Filament\Panel $panel): void
    {
        static::$accountPanel = $panel->getId();

        if (static::hasAccountFeatures()) {
            Livewire::component('filament.pages.accounts.create_account', CreateAccount::class);
            Livewire::component('filament.pages.accounts.accounts_settings', AccountSettings::class);
        }

        app()->bind(RegistrationResponseContract::class, FilamentAccountsRegistrationResponse::class);

        if (static::hasSocialiteFeatures()) {
            app()->bind(OAuthController::class, static function (Application $app) {
                return new OAuthController(
                    $app->make(CreatesUserFromProvider::class),
                    $app->make(CreatesConnectedAccounts::class),
                    $app->make(UpdatesConnectedAccounts::class),
                    $app->make(HandlesInvalidState::class),
                );
            });
        }

        if (static::$registersRoutes) {
            $panel->routes(fn () => $this->registerPublicRoutes());
            $panel->authenticatedRoutes(fn () => $this->registerAuthenticatedRoutes());
        }
    }

    public function boot(\Filament\Panel $panel): void
    {
        if (static::switchesCurrentAccount()) {
            Event::listen(TenantSet::class, SwitchCurrentAccount::class);
        }
    }
}