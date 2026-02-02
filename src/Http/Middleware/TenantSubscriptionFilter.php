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
            'path' => $request->path(),
            'url' => $request->url(),
            'ended' => $ended,
        ]);

        /*if ($ended) {
            return redirect(filament()->getCurrentPanel()->getTenantBillingUrl(filament()->getTenant()));
        }*/

        return $next($request);

    }
}
