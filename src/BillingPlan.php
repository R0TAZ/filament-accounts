<?php

namespace Rotaz\FilamentAccounts;

use Illuminate\Database\Eloquent\Model;

class BillingPlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'features',
        'monthly_price',
        'yearly_price',
        'onetime_price',
        'monthly_price_id',
        'yearly_price_id',
        'onetime_price_id',
        'trial',
        'default',
        'active',
    ];

    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FilamentAccounts::subscriptionModel(), 'billing_plan_id');
    }
}
