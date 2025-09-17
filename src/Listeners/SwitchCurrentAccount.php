<?php

namespace Rotaz\FilamentAccounts\Listeners;

use Filament\Events\TenantSet;
use Rotaz\FilamentAccounts\FilamentAccounts;
use Rotaz\FilamentAccounts\HasAccounts;


class SwitchCurrentAccount
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TenantSet $event): void
    {
        $tenant = $event->getTenant();

        /** @var HasAccounts $user */
        $user = $event->getUser();

        if (FilamentAccounts::switchesCurrentAccount() === false || ! in_array(HasAccounts::class, class_uses_recursive($user), true)) {
            return;
        }

        if (! $user->switchAccount($tenant) && ($fallbackAccount = $user->primaryAccount())) {
            $user->switchAccount($fallbackAccount);
        }
    }
}
