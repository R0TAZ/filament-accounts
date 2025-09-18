<?php

namespace Rotaz\FilamentAccounts\Concerns\Base;


use Rotaz\FilamentAccounts\Contracts\AddsAccountParties;
use Rotaz\FilamentAccounts\Contracts\CreatesAccounts;
use Rotaz\FilamentAccounts\Contracts\CreatesNewUsers;
use Rotaz\FilamentAccounts\Contracts\DeletesAccounts;
use Rotaz\FilamentAccounts\Contracts\DeletesUsers;
use Rotaz\FilamentAccounts\Contracts\InvitesAccountParties;
use Rotaz\FilamentAccounts\Contracts\RemovesAccountParties;
use Rotaz\FilamentAccounts\Contracts\UpdatesAccountNames;
use Rotaz\FilamentAccounts\Contracts\UpdatesUserPasswords;
use Rotaz\FilamentAccounts\Contracts\UpdatesUserProfileInformation;

trait HasBaseActionBindings
{
    /**
     * Register a class / callback that should be used to create new users.
     */
    public static function createUsersUsing(string $class): void
    {
        app()->singleton(CreatesNewUsers::class, $class);
    }

    /**
     * Register a class / callback that should be used to update user profile information.
     */
    public static function updateUserProfileInformationUsing(string $class): void
    {
        app()->singleton(UpdatesUserProfileInformation::class, $class);
    }

    /**
     * Register a class / callback that should be used to update user passwords.
     */
    public static function updateUserPasswordsUsing(string $class): void
    {
        app()->singleton(UpdatesUserPasswords::class, $class);
    }

    public static function createAccountsUsing(string $class): void
    {
        app()->singleton(CreatesAccounts::class, $class);
    }

    public static function updateAccountNamesUsing(string $class): void
    {
        app()->singleton(UpdatesAccountNames::class, $class);
    }

    public static function addAccountPartiesUsing(string $class): void
    {
        app()->singleton(AddsAccountParties::class, $class);
    }

    /**
     * Register a class / callback that should be used to add account employees.
     */
    public static function inviteAccountPartiesUsing(string $class): void
    {
        app()->singleton(InvitesAccountParties::class, $class);
    }

    public static function removeAccountPartiesUsing(string $class): void
    {
        app()->singleton(RemovesAccountParties::class, $class);
    }

    public static function deleteAccountsUsing(string $class): void
    {
        app()->singleton(DeletesAccounts::class, $class);
    }

    /**
     * Register a class / callback that should be used to delete users.
     */
    public static function deleteUsersUsing(string $class): void
    {
        app()->singleton(DeletesUsers::class, $class);
    }
}
