<?php

namespace App\Actions\FilamentAccounts;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Rotaz\FilamentAccounts\Contracts\UpdatesAccountNames;


class UpdateAccountName implements UpdatesAccountNames
{
    /**
     * Validate and update the given account's name.
     *
     * @param  array<string, string>  $input
     *
     * @throws AuthorizationException
     */
    public function update(User $user, Account $account, array $input): void
    {
        Gate::forUser($user)->authorize('update', $account);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('updateAccountName');

        $account->forceFill([
            'name' => $input['name'],
        ])->save();
    }
}
