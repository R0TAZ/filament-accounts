<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Account $account): bool
    {
        return $user->belongsToAccount($account);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Account $account): bool
    {
        return $user->ownsAccount($account);
    }

    public function addAccountParty(User $user, Account $account): bool
    {
        return $user->ownsAccount($account);
    }

    public function updateAccountParty(User $user, Account $account): bool
    {
        return $user->ownsAccount($account);
    }

    public function removeAccountParty(User $user, Account $account): bool
    {
        return $user->ownsAccount($account);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user,  Account $account): bool
    {
        return $user->ownsAccount($account);
    }
}
