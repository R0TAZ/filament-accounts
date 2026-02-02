<?php

namespace Rotaz\FilamentAccounts;

use Illuminate\Database\Eloquent\Model;

class BillingPlan extends Model
{
    protected $fillable = [
        'id',
    ];

    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FilamentAccounts::subscriptionModel(), 'billing_plan_id');
    }
}
