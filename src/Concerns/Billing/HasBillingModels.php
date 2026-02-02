<?php

namespace Rotaz\FilamentAccounts\Concerns\Billing;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Rotaz\FilamentAccounts\BillingPlan;
use Rotaz\FilamentAccounts\Contracts\CreatesSubscription;
use Rotaz\FilamentAccounts\Contracts\HasBilling;
use Rotaz\FilamentAccounts\Enums\SubscriptionCycle;
use Rotaz\FilamentAccounts\Subscriber;
use Rotaz\FilamentAccounts\Subscription;
use Rotaz\FilamentAccounts\SubscriptionInvoice;

trait HasBillingModels
{
    // Billing

    public static string $billingPlanModel = BillingPlan::class;

    public static string $subscriberModel = Subscriber::class;

    public static string $subscriptionModel = Subscription::class;

    public static string $subscriptionInvoiceModel = SubscriptionInvoice::class;

    public static function findBillingPlans(): mixed
    {
        return app(static::billingPlanModel())::where('active', true);
    }

    public static function createAccountSubsription(Model $billingPlan, SubscriptionCycle $cycles): mixed
    {
        try {

            $result = tap(filament()->getTenant(), function (HasBilling $billable) use ($billingPlan, $cycles) {

                return app(CreatesSubscription::class)->create($billable, $billingPlan, $cycles);

            });

            return $result;

        } catch (\Exception $exception) {
            Notification::make()->title('BILLING')->body($exception->getMessage())->send();

        }

        return null;

    }

    public static function subscriptionEnded(): bool
    {
        $subscription = filament()->getTenant()->subscription;
        if (! $subscription) {
            return true;
        }

        return Carbon::now()->isAfter($subscription->ends_at);

    }

    /**
     * Get the name of the user model used by the application.
     */
    public static function billingPlanModel(): string
    {
        return static::$billingPlanModel;
    }

    public static function subscriberModel(): string
    {
        return static::$subscriberModel;
    }

    public static function subscriptionModel(): string
    {
        return static::$subscriptionModel;
    }

    public static function subscriptionInvoiceModel(): string
    {
        return static::$subscriptionInvoiceModel;
    }

    public static function useBillingPlanModel(string $model): static
    {
        static::$billingPlanModel = $model;

        return new static;
    }

    public static function useSubscriberModel(string $model): static
    {
        static::$subscriberModel = $model;

        return new static;
    }

    public static function useSubscriptionModel(string $model): static
    {
        static::$subscriptionModel = $model;

        return new static;
    }

    public static function useSubscriptionInvoiceModel(string $model): static
    {
        static::$subscriptionInvoiceModel = $model;

        return new static;
    }
}
