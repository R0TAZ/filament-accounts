<?php

namespace Rotaz\FilamentAccounts\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Rotaz\FilamentAccounts\Events\AccountPartyUpdated;
use Rotaz\FilamentAccounts\FilamentAccounts;
use Rotaz\FilamentAccounts\Rules\Role;


class UpdateAccountPartyRole
{
    /**
     * Update the role for the given account employee.
     *
     * @throws AuthorizationException
     */
    public function update(mixed $user, mixed $account, int $accountPartyId, string $role): void
    {
        Gate::forUser($user)->authorize('updateAccountParty', $account);

        Validator::make(compact('role'), [
            'role' => ['required', 'string', new Role],
        ])->validate();

        $account->users()->updateExistingPivot($accountPartyId, compact('role'));

        AccountPartyUpdated::dispatch($account->fresh(), FilamentAccounts::findUserByIdOrFail($accountPartyId));
    }
}
