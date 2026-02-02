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


class DeleteSubscriptionForm extends Component
{

    public mixed $subscription;

    /**
     * Mount the component.
     */
    public function mount(mixed $subscription): void
    {
        $this->subscription = $subscription;
    }

    public function deleteSubscription(): void
    {

        $this->subscription->cancel();

        $this->subscription = null;

        $this->redirect(filament()->getTenantBillingUrl());
    }

    /**
     * Cancel the account deletion.
     */
    public function cancelSubscriptionDeletion(): void
    {
        $this->dispatch('close-modal', id: 'confirmingSubscriptionDeletion');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('filament-accounts::billing.delete-subscription-form');
    }

    public function subscriptionDeleted($subscription): void
    {
        $name = $subscription->name;

        Notification::make()
            ->title(__('filament-accounts::default.notifications.account_deleted.title'))
            ->success()
            ->body(Str::inlineMarkdown("A subscriçao {$name} foi excluida", compact('name')))
            ->send();
    }
}
