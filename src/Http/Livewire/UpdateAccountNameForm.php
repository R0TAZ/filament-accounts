<?php

namespace Rotaz\FilamentAccounts\Http\Livewire;

use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Rotaz\FilamentAccounts\Contracts\UpdatesAccountNames;
use Rotaz\FilamentAccounts\FilamentAccounts;


class UpdateAccountNameForm extends Component
{

    public mixed $account;

    /**
     * The component's state.
     */
    public array $state = [];

    /**
     * Mount the component.
     */
    public function mount(mixed $account): void
    {
        $this->account = $account;

        $this->state = $account->withoutRelations()->toArray();
    }

    /**
     * Update the company's name.
     */
    public function updateCompanyName(UpdatesAccountNames $updater): void
    {
        $this->resetErrorBag();

        $updater->update($this->user, $this->account, $this->state);

        if (FilamentAccounts::hasNotificationsFeature()) {
            if (method_exists($updater, 'accountNameUpdated')) {
                $updater->accountNameUpdated($this->user, $this->account, $this->state);
            } else {
                $this->accountNameUpdated($this->account);
            }
        }
    }

    protected function accountNameUpdated($account): void
    {
        $name = $account->name;

        Notification::make()
            ->title(__('filament-accounts::default.notifications.account_name_updated.title'))
            ->success()
            ->body(Str::inlineMarkdown(__('filament-accounts::default.notifications.account_name_updated.body', compact('name'))))
            ->send();
    }

    /**
     * Get the current user of the application.
     */
    public function getUserProperty(): ?Authenticatable
    {
        return Auth::user();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('filament-accounts::accounts.update-account-name-form');
    }
}
