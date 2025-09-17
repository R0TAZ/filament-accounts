<?php

namespace Rotaz\FilamentAccounts\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class AccountEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public mixed $account;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(mixed $account)
    {
        $this->account = $account;
    }
}
