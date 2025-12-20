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
        Schema::table('survey_completions', function (Blueprint $table) {
            // CPX payout in TZS (for profit calculation)
            $table->decimal('cpx_payout_tzs', 12, 2)->default(0)->after('cpx_payout');
            
            // VIP bonus amount
            $table->decimal('vip_bonus', 10, 2)->default(0)->after('user_reward');
            
            // Profit margin (cpx_payout_tzs - user_reward)
            $table->decimal('profit_margin', 12, 2)->default(0)->after('vip_bonus');
        });

        // Update enum to include 'screenout' status
        // For MySQL, we need to modify the enum
        DB::statement("ALTER TABLE survey_completions MODIFY COLUMN status ENUM('pending', 'completed', 'credited', 'rejected', 'reversed', 'screenout') DEFAULT 'pending'");

        // Update survey_type enum to include 'screenout'
        DB::statement("ALTER TABLE survey_completions MODIFY COLUMN survey_type ENUM('short', 'medium', 'long', 'screenout') DEFAULT 'short'");

        // Make survey_id and transaction_id nullable for screenouts
        Schema::table('survey_completions', function (Blueprint $table) {
            $table->string('survey_id')->nullable()->change();
            $table->string('transaction_id')->nullable()->change();
        });

        // Make user_id nullable for edge cases
        Schema::table('survey_completions', function (Blueprint $table) {
            // Drop foreign key first if exists
            try {
                $table->dropForeign(['user_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist
            }
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_completions', function (Blueprint $table) {
            $table->dropColumn(['cpx_payout_tzs', 'vip_bonus', 'profit_margin']);
        });

        // Revert enum changes
        DB::statement("ALTER TABLE survey_completions MODIFY COLUMN status ENUM('pending', 'completed', 'credited', 'rejected', 'reversed') DEFAULT 'pending'");
        DB::statement("ALTER TABLE survey_completions MODIFY COLUMN survey_type ENUM('short', 'medium', 'long') DEFAULT 'short'");
    }
};
