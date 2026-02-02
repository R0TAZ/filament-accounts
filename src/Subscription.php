<?php

namespace Rotaz\FilamentAccounts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Rotaz\FilamentAccounts\Enums\SubscriptionCycle;
use Rotaz\FilamentAccounts\Enums\SubscriptionInvoiceStatus;
use Rotaz\FilamentAccounts\Enums\SubscriptionStatus;
use Rotaz\FilamentAccounts\Utils\FormatterUtil;

class Subscription extends Model
{
    protected $fillable = [
        'billable_type',
        'billable_id',
        'billing_plan_id',
        'vendor_slug',
        'vendor_product_id',
        'vendor_transaction_id',
        'vendor_customer_id',
        'vendor_subscription_id',
        'cycle',
        'status',
        'seats',
        'trial_ends_at',
        'ends_at',
        'ended',
        'last_payment_at',
        'next_payment_at',
        'cancel_url',
        'update_url',
        'amount',
    ];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'cycle' => SubscriptionCycle::class,
        'cancelled_at' => 'datetime',
        'last_payment_at' => 'datetime',
        'next_payment_at' => 'datetime',
    ];

    public function subscriber(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FilamentAccounts::subscriberModel(), 'billable_id');
    }

    public function plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FilamentAccounts::billingPlanModel(), 'billing_plan_id');

    }

    public function getEndedAttribute(): bool
    {
        $is_ended = Carbon::now()->isAfter($this->ends_at);
        if (! $this->isFillable('ended')) {
            $this->attributes['ended'] = $is_ended;
        }

        return $is_ended;

    }

    public function cancel(): void
    {
        $this->status = SubscriptionStatus::CANCELLED;
        $this->invoices()->whereNot('status', SubscriptionInvoiceStatus::PAID)->update([
            'status' => SubscriptionInvoiceStatus::CANCELLED,
        ]);
        $this->save();
    }

    public function invoices()
    {
        return $this->hasMany(FilamentAccounts::subscriptionInvoiceModel(), 'subscription_id');

    }

    public function createInvoices(): void
    {
        $dn = Carbon::now();
        $ts = $dn->getTimestamp();
        if ($this->cycle == SubscriptionCycle::YEAR) {
            $price = $this->amount / 12;

            for ($month = 0; $month < 12; $month++) {
                $invoice_id = FormatterUtil::format_invoice_id($this->id, $month + 1, $ts);
                $data = [
                    'due_at' => $dn->addMonths($month + 1),
                    'amount' => $price,
                    'payload' => $this->generatePayment($invoice_id, $price),
                    'status' => SubscriptionInvoiceStatus::CREATED,
                    'invoice_id' => $invoice_id,
                ];
                $this->invoices()->create($data);
            }
        } else {
            $invoice_id = FormatterUtil::format_invoice_id($this->id, 1, $ts);
            $data = [
                'due_at' => $dn->addMonth(),
                'status' => SubscriptionInvoiceStatus::CREATED,
                'payload' => $this->generatePayment($invoice_id, $this->amount),
                'amount' => $this->amount,
                'invoice_id' => FormatterUtil::format_invoice_id($this->id, 1),
            ];
            $this->invoices()->create($data);
        }

    }

    protected function generatePayment($invoice, $amount)
    {
        $link = FormatterUtil::format_pix([
            'key' => 'acpte@g',
            'invoice_id' => $invoice,
            'amount' => $amount,
        ]);

        return [
            'pix_link' => $link,
            'bank_data' => [
                'acct_id' => '001',
                'acct_number' => '001',
                'acct_name' => 'KICONTA',
            ],
        ];

    }
}
