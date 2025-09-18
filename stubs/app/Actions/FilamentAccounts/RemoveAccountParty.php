<?php

namespace App\Actions\FilamentAccounts;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Rotaz\FilamentAccounts\Contracts\RemovesAccountParties;
use Rotaz\FilamentAccounts\Events\AccountPartyRemoved;

class RemoveAccountParty implements RemovesAccountParties
{
    /**
     * Remove the account employee from the given account.
     *
     * @throws AuthorizationException
     */
    public function remove(User $user, Account $account, User $accountParty): void
    {
        $this->authorize($user, $account, $accountParty);

        $this->ensureUserDoesNotOwnAccount($accountParty, $account);

        $account->removeUser($accountParty);

        AccountPartyRemoved::dispatch($account, $accountParty);
    }

    /**
     * Authorize that the user can remove the account employee.
     *
     * @throws AuthorizationException
     */
    protected function authorize(User $user, Account $account, User $accountParty): void
    {
        if (! Gate::forUser($user)->check('removeAccountParty', $account) &&
            $user->id !== $accountParty->id) {
            throw new AuthorizationException;
        }
    }

    /**
     * Ensure that the currently authenticated user does not own the account.
     */
    protected function ensureUserDoesNotOwnAccount(User $accountParty, Account $account): void
    {
        if ($accountParty->id === $account->owner->id) {
            throw ValidationException::withMessages([
                'account' => [__('filament-accounts::default.errors.cannot_leave_account')],
            ])->errorBag('removeAccountParty');
        }
    }
}
