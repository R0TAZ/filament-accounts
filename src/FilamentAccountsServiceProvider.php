<?php

namespace Rotaz\FilamentAccounts;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Rotaz\FilamentAccounts\Http\Livewire\AccountPartyManager;
use Rotaz\FilamentAccounts\Http\Livewire\ConnectedAccountsForm;
use Rotaz\FilamentAccounts\Http\Livewire\DeleteAccountForm;
use Rotaz\FilamentAccounts\Http\Livewire\DeleteUserForm;
use Rotaz\FilamentAccounts\Http\Livewire\LogoutOtherBrowserSessionsForm;
use Rotaz\FilamentAccounts\Http\Livewire\SetPasswordForm;
use Rotaz\FilamentAccounts\Http\Livewire\UpdateAccountNameForm;
use Rotaz\FilamentAccounts\Http\Livewire\UpdatePasswordForm;
use Rotaz\FilamentAccounts\Http\Livewire\UpdateProfileInformationForm;

class FilamentAccountsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-accounts');

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'filament-accounts');

        $this->configurePublishing();
        $this->configureCommands();

        $this->app->booted(function () {
            $this->configureComponents();
        });
    }

    protected function configureComponents(): void
    {
        $featureComponentMap = [
            'update-profile-information-form' => [FilamentAccounts::canUpdateProfileInformation(), UpdateProfileInformationForm::class],
            'update-password-form' => [FilamentAccounts::canUpdatePasswords(), UpdatePasswordForm::class],
            'delete-user-form' => [FilamentAccounts::hasAccountDeletionFeatures(), DeleteUserForm::class],
            'logout-other-browser-sessions-form' => [FilamentAccounts::canManageBrowserSessions(), LogoutOtherBrowserSessionsForm::class],
            'update-account-name-form' => [FilamentAccounts::hasAccountFeatures(), UpdateAccountNameForm::class],
            'account-party-manager' => [FilamentAccounts::hasAccountFeatures(), AccountPartyManager::class],
            'delete-account-form' => [FilamentAccounts::hasAccountFeatures(), DeleteAccountForm::class],
            'set-password-form' => [FilamentAccounts::canSetPasswords(), SetPasswordForm::class],
            'connected-accounts-form' => [FilamentAccounts::canManageConnectedAccounts(), ConnectedAccountsForm::class],
        ];

        foreach ($featureComponentMap as $alias => [$enabled, $component]) {
            if ($enabled) {
                Livewire::component($alias, $component);
            }
        }
    }

    protected function configurePublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-accounts'),
        ], 'filament-accounts-views');

        $this->publishes([
            __DIR__ . '/../lang' => lang_path('vendor/filament-accounts'),
        ], 'filament-accounts-translations');

        $this->publishes([
            __DIR__ . '/../database/migrations/0001_01_01_000000_create_users_table.php' => database_path('migrations/0001_01_01_000000_create_users_table.php'),
        ], 'filament-accounts-migrations');

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations/2024_05_21_100000_create_accounts_table.php' => database_path('migrations/2024_05_21_100000_create_accounts_table.php'),
            __DIR__ . '/../database/migrations/2024_05_21_200000_create_account_user_table.php' => database_path('migrations/2024_05_21_200000_create_account_user_table.php'),
            __DIR__ . '/../database/migrations/2024_05_21_300000_create_account_invitations_table.php' => database_path('migrations/2024_05_21_300000_create_account_invitations_table.php'),
        ], 'filament-accounts-account-migrations');

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations/2024_12_22_000000_create_connected_accounts_table.php' => database_path('migrations/2024_12_22_000000_create_connected_accounts_table.php'),
        ], 'filament-accounts-socialite-migrations');
    }

    protected function configureCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Console\InstallCommand::class,
        ]);
    }
}