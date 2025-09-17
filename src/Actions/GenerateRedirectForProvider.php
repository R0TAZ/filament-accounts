<?php

namespace Rotaz\FilamentAccounts\Actions;

use Laravel\Socialite\Facades\Socialite;
use Rotaz\FilamentAccounts\Contracts\GeneratesProviderRedirect;
use Symfony\Component\HttpFoundation\RedirectResponse;


class GenerateRedirectForProvider implements GeneratesProviderRedirect
{
    /**
     * Generates the redirect for a given provider.
     */
    public function generate(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }
}
