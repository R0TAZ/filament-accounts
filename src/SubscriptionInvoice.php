<?php

namespace Rotaz\FilamentAccounts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Rotaz\FilamentAccounts\Enums\SubscriptionInvoiceStatus;

class SubscriptionInvoice extends Model
{
    protected $casts = [
        'status' => SubscriptionInvoiceStatus::class,
        'payload' => 'array',
    ];

    protected $fillable = [
        'due_at',
        'invoice_id',
        'amount',
        'status',
        'payload',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(FilamentAccounts::subscriptionModel(), 'subscription_id');

    }

    public function registerPayInfo(): void
    {
        if ($this->status !== SubscriptionInvoiceStatus::CREATED) {
            return;
        }
        $this->status = SubscriptionInvoiceStatus::PENDING;
        $this->save();
    }
}
