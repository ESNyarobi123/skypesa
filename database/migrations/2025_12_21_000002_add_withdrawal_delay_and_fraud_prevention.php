<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds withdrawal processing delay and fraud prevention fields.
     * This allows time to detect fraud before actual payout.
     */
    public function up(): void
    {
        // Add columns to withdrawals only if they don't exist
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('withdrawals', 'processable_at')) {
                $table->timestamp('processable_at')
                    ->nullable()
                    ->after('created_at')
                    ->comment('Earliest time this withdrawal can be processed (for delay)');
            }
        });
        
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('withdrawals', 'delay_hours')) {
                $table->integer('delay_hours')
                    ->default(24)
                    ->after('processable_at')
                    ->comment('Hours of delay applied to this withdrawal');
            }
        });
        
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('withdrawals', 'risk_score')) {
                $table->integer('risk_score')
                    ->default(0)
                    ->after('admin_notes')
                    ->comment('Calculated fraud risk score (0-100)');
            }
        });
        
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('withdrawals', 'risk_factors')) {
                $table->json('risk_factors')
                    ->nullable()
                    ->after('risk_score')
                    ->comment('List of risk factors that contributed to score');
            }
        });
        
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('withdrawals', 'is_frozen')) {
                $table->boolean('is_frozen')
                    ->default(false)
                    ->after('risk_factors')
                    ->comment('If true, withdrawal is frozen pending review');
            }
        });
        
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('withdrawals', 'freeze_reason')) {
                $table->string('freeze_reason')
                    ->nullable()
                    ->after('is_frozen');
            }
        });
        
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('withdrawals', 'frozen_by')) {
                $table->foreignId('frozen_by')
                    ->nullable()
                    ->after('freeze_reason')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
        
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('withdrawals', 'frozen_at')) {
                $table->timestamp('frozen_at')
                    ->nullable()
                    ->after('frozen_by');
            }
        });

        // Add fraud tracking to users
        Schema::table('users', function (Blueprint $table) {
            $table->integer('fraud_score')
                ->default(0)
                ->comment('Cumulative fraud risk score for user');
            
            $table->integer('flagged_tasks')
                ->default(0)
                ->comment('Number of tasks flagged as suspicious');
            
            $table->boolean('is_suspicious')
                ->default(false)
                ->comment('User flagged for manual review');
            
            $table->timestamp('last_fraud_check')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropIndex(['status', 'processable_at', 'is_frozen']);
            $table->dropForeign(['frozen_by']);
            $table->dropColumn([
                'processable_at',
                'delay_hours',
                'risk_score',
                'risk_factors',
                'is_frozen',
                'freeze_reason',
                'frozen_by',
                'frozen_at',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'fraud_score',
                'flagged_tasks',
                'is_suspicious',
                'last_fraud_check',
            ]);
        });
    }
};
