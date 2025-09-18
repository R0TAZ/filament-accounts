<?php

namespace Rotaz\FilamentAccounts\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AddingAccount
{
    use Dispatchable;

    /**
     * The account owner.
     */
    public mixed $owner;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(mixed $owner)
    {
        $this->owner = $owner;
    }
}
