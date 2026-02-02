<?php

namespace Rotaz\FilamentAccounts\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rotaz\FilamentAccounts\Contracts\CreatesSubscription;
use Rotaz\FilamentAccounts\Contracts\HasBilling;
use Rotaz\FilamentAccounts\Enums\SubscriptionCycle;
use Rotaz\FilamentAccounts\Enums\SubscriptionStatus;
use Rotaz\FilamentAccounts\FilamentAccounts;

class CreateSubscription implements CreatesSubscription
{
    public function create(HasBilling $billable, \Illuminate\Database\Eloquent\Model $billingPlan, SubscriptionCycle $cycle): Model
    {

        Log::debug('CreateSubscription ...', [
            'billabe' => $billable,
            'plan' => $billingPlan,
        ]);

        $end_at = $cycle == SubscriptionCycle::YEAR ? Carbon::now()->addYear() : Carbon::now()->addMonth();
        $amount = $billingPlan->{$cycle->getFieldPrefix()};

        try {

            $subscription = DB::transaction(function () use ($billingPlan, $cycle, $billable, $amount, $end_at) {
                $subscription = $billable->subscriptions()->create([

                    'billable_type' => FilamentAccounts::subscriberModel(),
                    'billable_id' => $billable->modelKey(),
                    'billing_plan_id' => $billingPlan->getKey(),
                    'vendor_slug' => 'default',
                    'cycle' => $cycle,
                    'amount' => $amount,
                    'status' => SubscriptionStatus::ACTIVE,
                    'seats' => 1,
                    'ends_at' => $end_at,
                ]);

                $subscription->createInvoices();

                return $subscription;
            });

            return $subscription;

        } catch (\Throwable $e) {
            Log::critical('Failed to create subscription because of Exception', [
                'message' => $e->getMessage(),
                'billable' => $billable,
            ]);

            throw new \Exception($e->getMessage());
        }

    }
}
