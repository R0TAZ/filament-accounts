<?php

namespace Rotaz\FilamentAccounts\Http\Livewire;

use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Rotaz\FilamentAccounts\Contracts\SetsUserPasswords;
use Rotaz\FilamentAccounts\FilamentAccounts;

class SetPasswordForm extends Component
{
    /**
     * The component's state.
     *
     * @var array<string, mixed>
     */
    public $state = [
        'password' => '',
        'password_confirmation' => '',
    ];

    /**
     * Update the user's password.
     */
    public function setPassword(SetsUserPasswords $setter): void
    {
        $this->resetErrorBag();

        $setter->set($this->user, $this->state);

        $this->state = [
            'password' => '',
            'password_confirmation' => '',
        ];

        if (FilamentAccounts::hasNotificationsFeature()) {
            if (method_exists($setter, 'passwordSet')) {
                $setter->passwordSet($this->user, $this->state);
            } else {
                $this->passwordSet();
            }
        }
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
        return view('filament-accounts::profile.set-password-form');
    }

    public function passwordSet(): void
    {
        Notification::make()
            ->title(__('filament-accounts::default.notifications.password_set.title'))
            ->success()
            ->color(Color::Green)
            ->body(__('filament-accounts::default.notifications.password_set.body'))
            ->duration(3000)
            ->send();
    }
}
