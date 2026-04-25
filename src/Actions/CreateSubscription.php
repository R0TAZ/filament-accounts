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

        $amount   = $billingPlan->{$cycle->getFieldPrefix()};
        $isTrial  = (bool) $billingPlan->trial;
        $end_at   = $isTrial ? null : ($cycle == SubscriptionCycle::YEAR ? Carbon::now()->addYear() : Carbon::now()->addMonth());

        try {

            $subscription = DB::transaction(function () use ($billingPlan, $cycle, $billable, $amount, $isTrial, $end_at) {
                $subscription = $billable->subscriptions()->create([
                    'billable_type'  => FilamentAccounts::subscriberModel(),
                    'billable_id'    => $billable->modelKey(),
                    'billing_plan_id'=> $billingPlan->getKey(),
                    'vendor_slug'    => 'default',
                    'cycle'          => $cycle,
                    'amount'         => $amount,
                    'status'         => $isTrial ? SubscriptionStatus::TRIALING : SubscriptionStatus::ACTIVE,
                    'trial_ends_at'  => $isTrial ? Carbon::now()->addDays(config('filament-accounts.billing.trial_days', 30)) : null,
                    'seats'          => 1,
                    'ends_at'        => $end_at,
                ]);

                if ($amount > 0) {
                    $subscription->createInvoices();
                }

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
