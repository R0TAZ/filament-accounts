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
        Schema::create('accounts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('tax_id')->nullable();
            $table->boolean('personal_account')->nullable()->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
