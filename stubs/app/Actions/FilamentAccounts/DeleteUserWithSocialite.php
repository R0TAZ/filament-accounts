<?php

namespace App\Actions\FilamentAccounts;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Rotaz\FilamentAccounts\Contracts\DeletesAccounts;
use Rotaz\FilamentAccounts\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * The account deleter implementation.
     */
    protected DeletesAccounts $deletesAccounts;

    /**
     * Create a new action instance.
     */
    public function __construct(DeletesAccounts $deletesAccounts)
    {
        $this->deletesAccounts = $deletesAccounts;
    }

    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->deleteAccounts($user);
            $user->deleteProfilePhoto();
            $user->connectedAccounts->each(static fn ($account) => $account->delete());
            $user->tokens->each(static fn ($token) => $token->delete());
            $user->delete();
        });
    }

    /**
     * Delete the accounts and account associations attached to the user.
     */
    protected function deleteAccounts(User $user): void
    {
        $user->accounts()->detach();

        $user->ownedAccounts->each(function (Account $account) {
            $this->deletesAccounts->delete($account);
        });
    }
}
