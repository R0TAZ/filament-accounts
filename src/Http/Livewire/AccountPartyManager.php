<?php

namespace Rotaz\FilamentAccounts\Http\Livewire;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Rotaz\FilamentAccounts\Contracts\AddsAccountParties;
use Rotaz\FilamentAccounts\Contracts\InvitesAccountParties;
use Rotaz\FilamentAccounts\Contracts\RemovesAccountParties;
use Rotaz\FilamentAccounts\FilamentAccounts;
use Rotaz\FilamentAccounts\RedirectsActions;
use Rotaz\FilamentAccounts\Role;

class AccountPartyManager extends Component
{
    use RedirectsActions;

    public mixed $account;

    public mixed $managingRoleFor;

    /**
     * The current role for the user that is having their role managed.
     */
    public string $currentRole;

    public ?int $accountPartyIdBeingRemoved = null;

    public $addAccountPartyForm = [
        'email' => '',
        'role' => null,
    ];

    /**
     * Mount the component.
     */
    public function mount(mixed $account): void
    {
        $this->account = $account;
    }

    public function addAccountParty(InvitesAccountParties $inviter, AddsAccountParties $adder): void
    {
        $this->resetErrorBag();

        if (FilamentAccounts::sendsAccountInvitations()) {
            $inviter->invite(
                $this->user,
                $this->account,
                $this->addAccountPartyForm['email'],
                $this->addAccountPartyForm['role']
            );
        } else {
            $adder->add(
                $this->user,
                $this->account,
                $this->addAccountPartyForm['email'],
                $this->addAccountPartyForm['role']
            );
        }

        if (FilamentAccounts::hasNotificationsFeature()) {
            if (method_exists($inviter, 'accountInvitationSent')) {
                $inviter->accountInvitationSent(
                    $this->user,
                    $this->account,
                    $this->addAccountPartyForm['email'],
                    $this->addAccountPartyForm['role']
                );
            } else {
                $email = $this->addAccountPartyForm['email'];
                $this->accountInvitationSent($email);
            }
        }

        $this->addAccountPartyForm = [
            'email' => '',
            'role' => null,
        ];

        $this->account = $this->account->fresh();
    }

    /**
     * Cancel a pending account party invitation.
     */
    public function cancelAccountInvitation(int $invitationId): void
    {
        if (! empty($invitationId)) {
            $model = FilamentAccounts::accountInvitationModel();

            $model::whereKey($invitationId)->delete();
        }

        $this->account = $this->account->fresh();
    }

    /**
     * Allow the given user's role to be managed.
     */
    public function manageRole(int $userId): void
    {
        $this->dispatch('open-modal', id: 'currentlyManagingRole');
        $this->managingRoleFor = FilamentAccounts::findUserByIdOrFail($userId);
        $this->currentRole = $this->managingRoleFor->accountRole($this->account)->key;
    }

    /**
     * Save the role for the user being managed.
     *
     * @throws AuthorizationException
     */
    public function updateRole(UpdateAccountPartyRole $updater): void
    {
        $updater->update(
            $this->user,
            $this->account,
            $this->managingRoleFor->id,
            $this->currentRole
        );

        $this->account = $this->account->fresh();

        $this->dispatch('close-modal', id: 'currentlyManagingRole');
    }

    /**
     * Stop managing the role of a given user.
     */
    public function stopManagingRole(): void
    {
        $this->dispatch('close-modal', id: 'currentlyManagingRole');
    }


    public function confirmLeavingAccount(): void
    {
        $this->dispatch('open-modal', id: 'confirmingLeavingAccount');
    }

    /**
     * Remove the currently authenticated user from the company.
     */
    public function leaveAccount(RemovesAccountParties $remover): Response | Redirector | RedirectResponse
    {
        $remover->remove(
            $this->user,
            $this->account,
            $this->user
        );

        $this->dispatch('close-modal', id: 'confirmingLeavingAccount');

        $this->account = $this->account->fresh();

        if (! Auth::user()->fresh()->hasAnyAccounts() && ($tenantRegistrationUrl = Filament::getPanel(FilamentAccounts::getAccountPanel())?->getTenantRegistrationUrl())) {
            return redirect($tenantRegistrationUrl);
        }

        return $this->redirectPath($remover);
    }

    /**
     * Cancel leaving the account.
     */
    public function cancelLeavingAccount(): void
    {
        $this->dispatch('close-modal', id: 'confirmingLeavingAccount');
    }

    /**
     * Confirm that the given company employee should be removed.
     */
    public function confirmAccountPartyRemoval(int $userId): void
    {
        $this->dispatch('open-modal', id: 'confirmingAccountPartyRemoval');
        $this->accountPartyIdBeingRemoved = $userId;
    }

    /**
     * Remove a company employee from the company.
     */
    public function removeAccountParty(RemovesAccountParties $remover): void
    {
        $remover->remove(
            $this->user,
            $this->account,
            $user = FilamentAccounts::findUserByIdOrFail($this->accountPartyIdBeingRemoved)
        );

        $this->dispatch('close-modal', id: 'confirmingAccountPartyRemoval');

        $this->accountPartyIdBeingRemoved = null;

        $this->account = $this->account->fresh();
    }

    public function cancelAccountPartyRemoval(): void
    {
        $this->dispatch('close-modal', id: 'confirmingAccountPartyRemoval');
    }

    /**
     * Get the current user of the application.
     */
    public function getUserProperty(): ?Authenticatable
    {
        return Auth::user();
    }

    /**
     * Get the available company employee roles.
     */
    public function getRolesProperty(): array
    {
        return collect(FilamentAccounts::$roles)->transform(static function ($role) {
            return with($role->jsonSerialize(), static function ($data) {
                return (new Role(
                    $data['key'],
                    $data['name'],
                    $data['permissions']
                ))->description($data['description']);
            });
        })->values()->all();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('filament-accounts::accounts.account-party-manager');
    }

    public function accountInvitationSent($email): void
    {
        Notification::make()
            ->title(__('filament-accounts::default.notifications.account_invitation_sent.title'))
            ->success()
            ->body(Str::inlineMarkdown(__('filament-accounts::default.notifications.account_invitation_sent.body', compact('email'))))
            ->send();
    }
}
