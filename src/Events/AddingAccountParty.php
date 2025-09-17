<?php

namespace Rotaz\FilamentAccounts\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AddingAccountParty
{
    use Dispatchable;

    public mixed $account;

    public mixed $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(mixed $account, mixed $user)
    {
        $this->account = $account;
        $this->user = $user;
    }
}
