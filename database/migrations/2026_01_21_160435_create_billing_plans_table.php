<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('billing_plans', function (Blueprint $table) {
            $table->increments('id'); // Auto-incrementing UNSIGNED INTEGER (primary key)
            $table->string('name'); // VARCHAR equivalent column
            $table->text('description')->nullable(); // TEXT column, nullable for the description
            $table->string('features'); // VARCHAR equivalent column for features
            $table->string('monthly_price_id')->nullable();
            $table->string('yearly_price_id')->nullable();
            $table->string('onetime_price_id')->nullable();
            $table->boolean('active')->default(1);
            $table->boolean('trial')->default(0); // TINYINT equivalent column for a boolean, with a default value
            $table->boolean('default')->default(0); // TINYINT equivalent column for a boolean, with a default value
            $table->decimal('monthly_price')->nullable(); // VARCHAR equivalent column for the price
            $table->decimal('yearly_price')->nullable(); // VARCHAR equivalent column for the price
            $table->decimal('onetime_price')->nullable(); // VARCHAR equivalent column for the price
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_plans');
    }
};
