<?php

namespace Rotaz\FilamentAccounts\Gateways;

use Rotaz\FilamentAccounts\Contracts\PaymentGateway;
use Rotaz\FilamentAccounts\Subscription;
use Rotaz\FilamentAccounts\SubscriptionInvoice;
use Rotaz\FilamentAccounts\Utils\FormatterUtil;

class PixGateway implements PaymentGateway
{
    public function createCharge(Subscription $subscription, SubscriptionInvoice $invoice): array
    {
        $link = FormatterUtil::format_pix([
            'key'        => config('filament-accounts.billing.pix_key', ''),
            'invoice_id' => $invoice->invoice_id,
            'amount'     => $invoice->amount,
        ]);

        return [
            'pix_link' => $link,
            'bank_data' => [
                'acct_id'     => config('filament-accounts.billing.bank.acct_id', '001'),
                'acct_number' => config('filament-accounts.billing.bank.acct_number', '001'),
                'acct_name'   => config('filament-accounts.billing.bank.acct_name', ''),
            ],
        ];
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        return true;
    }

    public function handleWebhook(array $payload): void
    {
        //
    }
}