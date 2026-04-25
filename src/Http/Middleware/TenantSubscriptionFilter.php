<?php

namespace Rotaz\FilamentAccounts\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Rotaz\FilamentAccounts\FilamentAccounts;
use Symfony\Component\HttpFoundation\Response;

class TenantSubscriptionFilter
{
    public function handle(Request $request, Closure $next): Response
    {
        $ended = FilamentAccounts::subscriptionEnded();
        Log::debug('TenantSubscriptionFilter::handle', [
            'path'  => $request->path(),
            'url'   => $request->url(),
            'ended' => $ended,
        ]);

        if ($ended) {
            $billingUrl = filament()->getCurrentPanel()->getTenantBillingUrl(filament()->getTenant());

            // Avoid redirect loop: let billing-related routes through
            if ($billingUrl && ! str_starts_with($request->url(), $billingUrl)) {
                return redirect($billingUrl);
            }
        }

        return $next($request);
    }
}
