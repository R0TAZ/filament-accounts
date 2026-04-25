<?php

namespace Rotaz\FilamentAccounts\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Rotaz\FilamentAccounts\FilamentAccounts;
use Symfony\Component\HttpFoundation\Response;

class TenantSubscriptionFilter
{
    public function handle(Request $request, Closure $next): Response
    {
        // Billing and subscription pages must pass freely to avoid redirect loop
        if ($request->routeIs('filament.account.tenant.billing', 'filament.account.pages.subscription')) {
            return $next($request);
        }

        if (FilamentAccounts::subscriptionEnded()) {
            return redirect(filament()->getCurrentPanel()->getTenantBillingUrl(filament()->getTenant()));
        }

        return $next($request);
    }
}
