<?php

namespace Rotaz\FilamentAccounts\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ValidateAccountDeletion
{
    /**
     * @throws AuthorizationException
     */
    public function validate(mixed $user, mixed $account): void
    {
        Gate::forUser($user)->authorize('delete', $account);

        if ($account->personal_account) {
            throw ValidationException::withMessages([
                'account' => __('filament-accounts::default.errors.account_deletion'),
            ])->errorBag('deleteAccount');
        }
    }
}
