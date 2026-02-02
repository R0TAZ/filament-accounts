<?php

namespace Rotaz\FilamentAccounts\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BillingCreated extends AbstractBillingEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

}
