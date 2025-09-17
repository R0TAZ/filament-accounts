<?php

namespace Rotaz\FilamentAccounts\Http\Livewire;

use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Rotaz\FilamentAccounts\Contracts\CreatesAccounts;
use Rotaz\FilamentAccounts\RedirectsActions;


class CreateAccountForm extends Component
{
    use RedirectsActions;

    /**
     * The component's state.
     */
    public array $state = [];

    /**
     * Create a new company.
     */
    public function createCompany(CreatesAccounts $creator): Response | Redirector | RedirectResponse
    {
        $this->resetErrorBag();

        $creator->create($this->user, $this->state);

        $name = $this->state['name'];

        $this->accountCreated($name);

        return $this->redirectPath($creator);
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
        return view('filament-accounts::accounts.create-account-form');
    }

    public function accountsCreated($name): void
    {
        Notification::make()
            ->title(__('filament-accounts::default.notifications.account_created.title'))
            ->success()
            ->body(Str::inlineMarkdown(__('filament-accounts::default.notifications.account_created.body', compact('name'))))
            ->send();
    }
}
