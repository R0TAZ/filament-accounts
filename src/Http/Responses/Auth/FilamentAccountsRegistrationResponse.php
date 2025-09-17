<?php

namespace Rotaz\FilamentAccounts\Http\Responses\Auth;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\RegistrationResponse as FilamentRegistrationResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Rotaz\FilamentAccounts\FilamentAccounts;


class FilamentAccountsRegistrationResponse extends FilamentRegistrationResponse
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        $user = Filament::auth()->user();

        if (
            FilamentAccounts::autoAcceptsInvitations() &&
            method_exists($user, 'hasAnyAccounts') &&
            ! $user->hasAnyAccounts() &&
            ($invitation = FilamentAccounts::accountInvitationModel()::where('email', $user->email)->first())
        ) {
            return redirect(FilamentAccounts::generateAcceptInvitationUrl($invitation));
        }

        return parent::toResponse($request);
    }
}
