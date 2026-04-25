<?php

namespace Rotaz\FilamentAccounts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Rotaz\FilamentAccounts\Enums\SubscriptionInvoiceStatus;

class SubscriptionInvoice extends Model
{
    protected $casts = [
        'status'  => SubscriptionInvoiceStatus::class,
        'payload' => 'array',
        'paid_at' => 'datetime',
        'due_at'  => 'datetime',
    ];

    protected $fillable = [
        'invoice_id',
        'type',
        'subscription_id',
        'payload',
        'amount',
        'status',
        'paid_at',
        'due_at',
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
