<?php

namespace Rotaz\FilamentAccounts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Rotaz\FilamentAccounts\Enums\SubscriptionStatus;
use Rotaz\FilamentAccounts\FilamentAccounts;
use Rotaz\FilamentAccounts\Notifications\TrialEndingNotification;

class NotifyTrialEndingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of days before trial end to send the notification.
     */
    public function __construct(public readonly int $daysBefore = 3) {}

    public function handle(): void
    {
        $targetDate = now()->addDays($this->daysBefore)->toDateString();

        app(FilamentAccounts::subscriptionModel())::query()
            ->where('status', SubscriptionStatus::TRIALING)
            ->whereDate('trial_ends_at', $targetDate)
            ->with('subscriber')
            ->each(function ($subscription) {
                $notifiable = $subscription->subscriber;
                if ($notifiable) {
                    Notification::send($notifiable, new TrialEndingNotification($subscription));
                }
            });
    }
}