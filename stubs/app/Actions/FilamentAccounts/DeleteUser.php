<?php

namespace App\Actions\FilamentAccounts;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Rotaz\FilamentAccounts\Contracts\DeletesAccounts;
use Rotaz\FilamentAccounts\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Create a new action instance.
     */
    public function __construct(protected DeletesAccounts $deletesAccounts)
    {
        //
    }

    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->deleteAccounts($user);
            $user->deleteProfilePhoto();
            $user->tokens->each(static fn (PersonalAccessToken $token) => $token->delete());
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
