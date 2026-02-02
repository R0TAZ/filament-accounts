<?php

namespace Rotaz\FilamentAccounts\Http\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Rotaz\FilamentAccounts\Enums\SubscriptionCycle;

class BillingCheckout extends Component
{
    public $billing_cycle_available = 'both'; // month, year, or both;

    public $billing_cycle_selected = 'month';

    public $billing_provider;

    public $change = false;

    public $userSubscription = null;

    public $userPlan = null;

    public $plans = null;

    public $selectedPlan = null;

    public function mount($plans): void
    {
        $this->plans = $plans;
        Log::debug('Load plans ', $this->plans);
    }

    public function selectPlan($plan_id): void
    {
        $plan = $this->plans[$plan_id]->toArray();
        $cycle = SubscriptionCycle::from($this->billing_cycle_selected);

        $this->selectedPlan = [
            'billing_plan_id' => $plan['id'],
            'name' => $plan['name'],
            'description' => $plan['description'],
            'features' => explode(',', $plan['features']),
            'cycle' => $cycle->getLabel(),
            'amount' => $plan[$cycle->getFieldPrefix()],
        ];

    }

    public function render()
    {
        return view('filament-accounts::billing.billing-checkout');
    }
}
