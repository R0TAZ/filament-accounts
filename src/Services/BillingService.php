<?php

namespace Rotaz\FilamentAccounts\Services;

use Filament\Billing\Providers\Contracts\Provider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Rotaz\FilamentAccounts\Http\Middleware\TenantSubscriptionFilter;
use Rotaz\FilamentAccounts\Pages\Billing\Subscription;
use Rotaz\FilamentAccounts\Pages\Billing\SubscriptionManager;

class BillingService implements Provider
{
    public function getRouteAction(): \Closure
    {

        return function (): RedirectResponse {
            Log::debug('Get Route ', [
                filament()->getTenant()->getKey(),
            ]);

            return redirect(Subscription::getUrl());

        };
    }

    public function getSubscribedMiddleware(): string
    {
        return TenantSubscriptionFilter::class;
    }

}
