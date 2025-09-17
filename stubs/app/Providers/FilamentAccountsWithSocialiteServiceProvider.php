<?php

namespace App\Providers;

use App\Actions\FilamentAccounts\AddAccountParty;
use App\Actions\FilamentAccounts\CreateAccount;
use App\Actions\FilamentAccounts\CreateConnectedAccount;
use App\Actions\FilamentAccounts\CreateNewUser;
use App\Actions\FilamentAccounts\CreateUserFromProvider;
use App\Actions\FilamentAccounts\DeleteUser;
use App\Actions\FilamentAccounts\HandleInvalidState;
use App\Actions\FilamentAccounts\ResolveSocialiteUser;
use App\Actions\FilamentAccounts\SetUserPassword;
use App\Actions\FilamentAccounts\UpdateCompanyName;
use App\Actions\FilamentAccounts\UpdateConnectedAccount;
use App\Actions\FilamentAccounts\UpdateUserPassword;
use App\Actions\FilamentAccounts\UpdateUserProfileInformation;
use App\Models\Account;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Rotaz\FilamentAccounts\Actions\GenerateRedirectForProvider;
use Rotaz\FilamentAccounts\Contracts\DeletesAccounts;
use Rotaz\FilamentAccounts\Contracts\InvitesAccountParties;
use Rotaz\FilamentAccounts\Contracts\RemovesAccountParties;
use Rotaz\FilamentAccounts\Enums\Feature;
use Rotaz\FilamentAccounts\Enums\Provider;
use Rotaz\FilamentAccounts\FilamentAccounts;
use Rotaz\FilamentAccounts\Pages\Account\AccountSettings;
use Rotaz\FilamentAccounts\Pages\Auth\Login;
use Rotaz\FilamentAccounts\Pages\Auth\Register;


class FilamentAccountsServiceProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('account')
            ->path('account')
            ->default()
            ->login(Login::class)
            ->passwordReset()
            ->homeUrl(static function (): ?string {
                $user = Auth::user();

                if ($account = $user?->primaryAccount()) {
                    return Pages\Dashboard::getUrl(panel: FilamentAccounts::getAccountPanel(), tenant: $account);
                }

                return Filament::getPanel(FilamentAccounts::getAccountPanel())->getTenantRegistrationUrl();
            })
            ->plugin(
                FilamentAccounts::make()
                    ->userPanel('user')
                    ->switchCurrentAccount()
                    ->updateProfileInformation()
                    ->updatePasswords()
                    ->setPasswords()
                    ->connectedAccounts()
                    ->manageBrowserSessions()
                    ->accountDeletion()
                    ->profilePhotos()
                    ->api()
                    ->accounts(invitations: true)
                    ->autoAcceptInvitations()
                    ->termsAndPrivacyPolicy()
                    ->notifications()
                    ->modals()
                    ->socialite(
                        providers: [Provider::Github],
                        features: [Feature::RememberSession, Feature::ProviderAvatars],
                    ),
            )
            ->registration(Register::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->tenant(Account::class)
            ->tenantProfile(AccountSettings::class)
            ->tenantRegistration(CreateAccount::class)
            ->discoverResources(in: app_path('Filament/Account/Resources'), for: 'App\\Filament\\Account\\Resources')
            ->discoverPages(in: app_path('Filament/Account/Pages'), for: 'App\\Filament\\Account\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('Profile')
                    ->icon('heroicon-o-user-circle')
                    ->url(static fn () => \Rotaz\FilamentAccounts\Pages\User\Profile::getUrl(panel: FilamentAccounts::getUserPanel())),
            ])
            ->authGuard('web')
            ->discoverWidgets(in: app_path('Filament/Account/Widgets'), for: 'App\\Filament\\Account\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        FilamentAccounts::createUsersUsing(CreateNewUser::class);
        FilamentAccounts::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        FilamentAccounts::updateUserPasswordsUsing(UpdateUserPassword::class);

        FilamentAccounts::createAccountsUsing(CreateAccount::class);
        FilamentAccounts::updateAccountNamesUsing(UpdateCompanyName::class);
        FilamentAccounts::addAccountPartiesUsing(AddAccountParty::class);
        FilamentAccounts::inviteAccountPartiesUsing(InvitesAccountParties::class);
        FilamentAccounts::removeAccountPartiesUsing(RemovesAccountParties::class);
        FilamentAccounts::deleteAccountsUsing(DeletesAccounts::class);
        FilamentAccounts::deleteUsersUsing(DeleteUser::class);

        FilamentAccounts::resolvesSocialiteUsersUsing(ResolveSocialiteUser::class);
        FilamentAccounts::createUsersFromProviderUsing(CreateUserFromProvider::class);
        FilamentAccounts::createConnectedAccountsUsing(CreateConnectedAccount::class);
        FilamentAccounts::updateConnectedAccountsUsing(UpdateConnectedAccount::class);
        FilamentAccounts::setUserPasswordsUsing(SetUserPassword::class);
        FilamentAccounts::handlesInvalidStateUsing(HandleInvalidState::class);
        FilamentAccounts::generatesProvidersRedirectsUsing(GenerateRedirectForProvider::class);
    }

    /**
     * Configure the roles and permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        FilamentAccounts::defaultApiTokenPermissions(['read']);

        FilamentAccounts::role('admin', 'Administrator', [
            'create',
            'read',
            'update',
            'delete',
        ])->description('Administrator users can perform any action.');

        FilamentAccounts::role('operator', 'Operator', [
            'read',
            'create',
            'update',
        ])->description('Operator users have the ability to read, create, and update.');
    }
}
