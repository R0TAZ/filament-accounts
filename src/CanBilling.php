<?php

namespace Rotaz\FilamentAccounts;

use Rotaz\FilamentAccounts\Enums\SubscriptionStatus;

trait CanBilling
{
    public function subscriber(): bool
    {
        return $this->subscriptions()->whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIALING])->exists();
    }

    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FilamentAccounts::subscriptionModel(), 'billable_id')->where('billable_type', FilamentAccounts::subscriberModel());
    }

    public function subscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FilamentAccounts::subscriptionModel(), 'billable_id')->where('status', SubscriptionStatus::ACTIVE)->orderBy('created_at', 'desc');
    }

    public function currentSubscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FilamentAccounts::subscriptionModel(), 'billable_id')
            ->whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIALING])
            ->orderBy('created_at', 'desc');
    }

    public function modelKey()
    {
        return $this->getKey();
    }

    public function modelClass(): string
    {
        return static::class;
    }



}
