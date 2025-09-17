<?php

namespace Rotaz\FilamentAccounts\Http\Controllers;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Features\SupportRedirects\Redirector;
use Rotaz\FilamentAccounts\Contracts\AddsAccountParties;
use Rotaz\FilamentAccounts\FilamentAccounts;

class AccountInvitationController extends Controller
{
    /**
     * Accept a company invitation.
     */
    public function accept(Request $request, int|string $invitationId): Redirector | RedirectResponse | null
    {
        $model = FilamentAccounts::accountInvitationModel();

        $invitation = $model::whereKey($invitationId)->firstOrFail();
        $user = FilamentAccounts::userModel()::where('email', $invitation->email)->first();

        app(AddsAccountParties::class::class)->add(
            $invitation->account->owner,
            $invitation->account,
            $invitation->email,
            $invitation->role
        );

        $invitation->delete();

        $title = __('filament-accounts::default.banner.account_invitation_accepted', ['account' => $invitation->account->name]);
        $notification = Notification::make()->title(Str::inlineMarkdown($title))->success()->persistent()->send();

        if ($user) {
            Filament::auth()->login($user);

            return redirect(url(filament()->getHomeUrl()))->with('notification.success.account_invitation_accepted', $notification);
        }

        return redirect(url(filament()->getLoginUrl()));
    }

    /**
     * Cancel the given company invitation.
     *
     * @throws AuthorizationException
     */
    public function destroy(Request $request, int $invitationId): Redirector | RedirectResponse
    {
        $model = FilamentAccounts::accountInvitationModel();

        $invitation = $model::whereKey($invitationId)->firstOrFail();

        if (! Gate::forUser($request->user())->check('removeAccountParty', $invitation->account)) {
            throw new AuthorizationException;
        }

        $invitation->delete();

        return back(303);
    }
}
