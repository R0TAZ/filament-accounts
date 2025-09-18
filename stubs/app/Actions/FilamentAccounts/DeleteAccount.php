<?php

namespace App\Actions\FilamentAccounts;

use App\Models\Account;
use Rotaz\FilamentAccounts\Contracts\DeletesAccounts;

class DeleteAccount implements DeletesAccounts
{
    /**
     * Delete the given account.
     */
    public function delete(Account $account): void
    {
        $account->purge();
    }
}
