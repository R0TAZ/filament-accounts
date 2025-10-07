<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Rotaz\FilamentAccounts\FilamentAccounts;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        if( ! (Schema::hasTable('users'))) {

            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique()->nullable();
                $table->string('phone')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password')->nullable();
                $table->rememberToken();
                $table->foreignId('current_company_id')->nullable();
                $table->foreignId('current_connected_account_id')->nullable();
                $table->string('profile_photo_path', 2048)->nullable();
                $table->timestamps();
            });

        }else{

            Schema::table('users', function (Blueprint $table) {

                if (!Schema::hasColumn('users', 'profile_photo_path')) {
                    $table->string('profile_photo_path', 2048)->nullable();
                }
                if (!Schema::hasColumn('users', 'current_company_id')) {
                    $table->foreignId('current_company_id')->nullable();
                }
                if (!Schema::hasColumn('users', 'current_connected_account_id')) {
                    $table->foreignId('current_connected_account_id')->nullable();
                }
                if (!Schema::hasColumn('users', 'phone')) {
                    $table->string('phone')->nullable();
                }

                $table->string('email')->nullable()->change();
                $table->string('password')->nullable()->change();
                $table->rememberToken()->nullable()->change();


            });



         }



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'profile_photo_path') || Schema::hasColumn('users', 'current_company_id') || Schema::hasColumn('users', 'current_connected_account_id') || Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'profile_photo_path')) {
                    $table->dropColumn('profile_photo_path');
                }
                if (Schema::hasColumn('users', 'current_company_id')) {
                    $table->dropColumn('current_company_id');
                }
                if (Schema::hasColumn('users', 'current_connected_account_id')) {
                    $table->dropColumn('current_connected_account_id');
                }
                if (Schema::hasColumn('users', 'phone')) {
                    $table->dropColumn('phone');
                }
            });
        }
    }
};
