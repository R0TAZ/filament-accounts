<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Rotaz\FilamentAccounts\Enums\PaymentMethodType;
use Rotaz\FilamentAccounts\Enums\SubscriptionInvoiceStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id');
            $table->string('type')->nullable()->default(PaymentMethodType::PIX_QRCODE->value);
            $table->foreignId('subscription_id')->constrained();
            $table->jsonb('payload')->nullable();
            $table->decimal('amount');
            $table->string('status')->default(SubscriptionInvoiceStatus::CREATED->value)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
    }
};
