<?php

namespace App\Actions\FilamentAccounts;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Rotaz\FilamentAccounts\Contracts\CreatesAccounts;
use Rotaz\FilamentAccounts\Events\AddingAccount;
use Rotaz\FilamentAccounts\FilamentAccounts;

class CreateAccount implements CreatesAccounts
{
    /**
     * Validate and create a new account for the given user.
     *
     * @param  array<string, string>  $input
     *
     * @throws AuthorizationException
     */
    public function create(User $user, array $input): Account
    {
        Gate::forUser($user)->authorize('create', FilamentAccounts::newAccountModel());

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('createAccount');

        AddingAccount::dispatch($user);

        $user->switchAccount($account = $user->ownedAccounts()->create([
            'name' => $input['name'],
            'personal_account' => false,
        ]));

        return $account;
    }
}
