<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the category enum to include survey categories
        DB::statement("ALTER TABLE transactions MODIFY COLUMN category ENUM(
            'task_reward',
            'withdrawal',
            'withdrawal_fee',
            'deposit',
            'subscription',
            'bonus',
            'refund',
            'adjustment',
            'survey_reward',
            'survey_reversal',
            'referral_bonus'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN category ENUM(
            'task_reward',
            'withdrawal',
            'withdrawal_fee',
            'deposit',
            'subscription',
            'bonus',
            'refund',
            'adjustment'
        )");
    }
};
