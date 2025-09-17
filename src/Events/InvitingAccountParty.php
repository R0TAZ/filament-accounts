<?php

namespace Rotaz\FilamentAccounts\Events;

use Illuminate\Foundation\Events\Dispatchable;

class InvitingAccountParty
{
    use Dispatchable;

    public mixed $account;

    /**
     * The email address of the invitee.
     */
    public string $email;

    /**
     * The role of the invitee.
     */
    public ?string $role = null;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(mixed $account, string $email, ?string $role = null)
    {
        $this->account = $account;
        $this->email = $email;
        $this->role = $role;
    }
}
