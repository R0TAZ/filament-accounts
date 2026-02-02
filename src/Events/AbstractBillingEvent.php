<?php

namespace Rotaz\FilamentAccounts\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AbstractBillingEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public ?array $message, public ?array $context = [])
    {
        //
    }

    public function getUserId()
    {
        return $this->context['user_id'] ?? null;
    }

    public function getTenantId()
    {
        return $this->context['tenant_id'] ?? null;

    }
}
