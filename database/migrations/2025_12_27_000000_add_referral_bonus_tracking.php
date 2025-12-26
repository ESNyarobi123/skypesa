<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds fields to track referral bonus payments.
     * The referral_bonus_paid flag tracks whether the referrer has been paid.
     * This allows us to require multiple task completions before paying the bonus.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Track if referral bonus has been paid to this user's referrer
            $table->boolean('referral_bonus_paid')->default(false)->after('first_task_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('referral_bonus_paid');
        });
    }
};
