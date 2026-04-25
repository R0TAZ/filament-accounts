<?php

namespace Rotaz\FilamentAccounts\Listeners;

use Illuminate\Support\Facades\Log;
use Rotaz\FilamentAccounts\Contracts\CreatesSubscription;
use Rotaz\FilamentAccounts\Enums\SubscriptionCycle;
use Rotaz\FilamentAccounts\Events\AccountCreated;
use Rotaz\FilamentAccounts\FilamentAccounts;

class CreateInitialSubscription
{
    public function handle(AccountCreated $event): void
    {
        $planId = FilamentAccounts::$initialSubscriptionPlanId;
        if (! $planId) {
            return;
        }

        $plan = app(FilamentAccounts::billingPlanModel())::find($planId);
        if (! $plan) {
            Log::warning('CreateInitialSubscription: plan not found', ['plan_id' => $planId]);
            return;
        }

        try {
            app(CreatesSubscription::class)->create(
                $event->account,
                $plan,
                SubscriptionCycle::MONTH,
            );
        } catch (\Throwable $e) {
            Log::error('CreateInitialSubscription: failed', ['message' => $e->getMessage()]);
        }
    }
}