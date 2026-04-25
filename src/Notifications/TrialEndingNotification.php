<?php

namespace Rotaz\FilamentAccounts\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Rotaz\FilamentAccounts\Subscription;

class TrialEndingNotification extends Notification
{
    public function __construct(public readonly Subscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $endsAt = $this->subscription->trial_ends_at;

        return (new MailMessage)
            ->subject(__('Your trial is ending soon'))
            ->line(__('Your trial period ends on :date.', ['date' => $endsAt?->format('d/m/Y')]))
            ->line(__('Subscribe to a plan to keep your access.'))
            ->action(__('Choose a plan'), filament()->getUrl());
    }
}