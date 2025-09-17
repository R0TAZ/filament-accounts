<?php

namespace Rotaz\FilamentAccounts\Http\Livewire;

use Filament\Notifications\Notification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Rotaz\FilamentAccounts\Contracts\DeletesAccounts;
use Rotaz\FilamentAccounts\FilamentAccounts;
use Rotaz\FilamentAccounts\RedirectsActions;


class DeleteAccountForm extends Component
{
    use RedirectsActions;

    public mixed $account;

    /**
     * Mount the component.
     */
    public function mount(mixed $account): void
    {
        $this->account = $account;
    }

    /**
     * Delete the $account.
     *
     * @throws AuthorizationException
     */
    public function deleteAccount(ValidateAccountDeletion $validator, DeletesAccounts $deleter): Response | Redirector | RedirectResponse
    {
        $validator->validate(Auth::user(), $this->account);

        $deleter->delete($this->account);

        if (FilamentAccounts::hasNotificationsFeature()) {
            if (method_exists($deleter, 'accountDeleted')) {
                $deleter->accountDeleted($this->account);
            } else {
                $this->accountDeleted($this->account);
            }
        }

        $this->account = null;

        return $this->redirectPath($deleter);
    }

    /**
     * Cancel the company deletion.
     */
    public function cancelAccountDeletion(): void
    {
        $this->dispatch('close-modal', id: 'confirmingAccountDeletion');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('filament-accounts::accounts.delete-account-form');
    }

    public function accountsDeleted($account): void
    {
        $name = $account->name;

        Notification::make()
            ->title(__('filament-accounts::default.notifications.account_deleted.title'))
            ->success()
            ->body(Str::inlineMarkdown(__('filament-accounts::default.notifications.account_deleted.body', compact('name'))))
            ->send();
    }
}
