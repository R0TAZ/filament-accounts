<?php

namespace Rotaz\FilamentAccounts\Contracts;

use Rotaz\FilamentAccounts\Enums\SubscriptionCycle;
use Rotaz\FilamentAccounts\Subscription;

/**
 * @method \Illuminate\Database\Eloquent\Model create(HasBilling $billable, \Illuminate\Database\Eloquent\Model $billingPlan, SubscriptionCycle $cycle)
 */
interface CreatesSubscription {}
