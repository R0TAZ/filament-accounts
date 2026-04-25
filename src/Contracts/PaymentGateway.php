<?php

namespace Rotaz\FilamentAccounts\Contracts;

use Rotaz\FilamentAccounts\Subscription;
use Rotaz\FilamentAccounts\SubscriptionInvoice;

interface PaymentGateway
{
    public function createCharge(Subscription $subscription, SubscriptionInvoice $invoice): array;

    public function cancelSubscription(Subscription $subscription): bool;

    public function handleWebhook(array $payload): void;
}