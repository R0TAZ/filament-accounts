<?php

namespace Rotaz\FilamentAccounts\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User;
use Rotaz\FilamentAccounts\ConnectedAccount;


interface UpdatesConnectedAccounts
{
    /**
     * Update a given connected account.
     */
    public function update(Authenticatable $user, ConnectedAccount $connectedAccount, string $provider, User $providerUser): ConnectedAccount;
}
