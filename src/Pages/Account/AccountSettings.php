<?php

namespace Rotaz\FilamentAccounts\Pages\Company;

use Filament\Facades\Filament;
use Filament\Pages\Tenancy\EditTenantProfile as BaseEditTenantProfile;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

use function Filament\authorize;

class AccountSettings extends BaseEditTenantProfile
{
    protected static string $view = 'filament-accounts::filament.pages.accounts.account_settings';

    public static function getLabel(): string
    {
        return __('filament-accounts::default.pages.titles.account_settings');
    }

    public static function canView(Model $tenant): bool
    {
        try {
            return authorize('view', $tenant)->allowed();
        } catch (AuthorizationException $exception) {
            return $exception->toResponse()->allowed();
        }
    }

    protected function getViewData(): array
    {
        return [
            'account' => Filament::getTenant(),
        ];
    }
}
