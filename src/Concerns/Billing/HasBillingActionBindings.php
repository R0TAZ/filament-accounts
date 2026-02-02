<?php

namespace Rotaz\FilamentAccounts\Concerns\Billing;


use Rotaz\FilamentAccounts\Contracts\AddsAccountParties;
use Rotaz\FilamentAccounts\Contracts\CreatesAccounts;
use Rotaz\FilamentAccounts\Contracts\CreatesNewUsers;
use Rotaz\FilamentAccounts\Contracts\CreatesSubscription;
use Rotaz\FilamentAccounts\Contracts\DeletesAccounts;
use Rotaz\FilamentAccounts\Contracts\DeletesUsers;
use Rotaz\FilamentAccounts\Contracts\InvitesAccountParties;
use Rotaz\FilamentAccounts\Contracts\RemovesAccountParties;
use Rotaz\FilamentAccounts\Contracts\UpdatesAccountNames;
use Rotaz\FilamentAccounts\Contracts\UpdatesUserPasswords;
use Rotaz\FilamentAccounts\Contracts\UpdatesUserProfileInformation;

trait HasBillingActionBindings
{
    /**
     * Register a class / callback that should be used to create new users.
     */
    public static function createSubscriptionsUsing(string $class): void
    {
        app()->singleton(CreatesSubscription::class, $class);
    }

}
