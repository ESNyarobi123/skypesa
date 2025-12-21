<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds task categorization (traffic_task vs conversion_task) and
     * enhanced tracking fields for postback-driven payouts.
     */
    public function up(): void
    {
        // Add category to tasks table
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('category', ['traffic_task', 'conversion_task'])
                ->default('traffic_task')
                ->after('type')
                ->comment('Traffic tasks have smaller rewards with strict limits; conversion tasks have bigger rewards requiring postback confirmation');
            
            $table->boolean('require_postback')
                ->default(false)
                ->after('category')
                ->comment('If true, payout only happens when postback is received from provider');
            
            $table->decimal('min_payout', 10, 4)
                ->nullable()
                ->after('reward_override')
                ->comment('Minimum expected payout from provider (for conversion tasks)');
            
            $table->integer('ip_daily_limit')
                ->nullable()
                ->after('daily_limit')
                ->comment('Maximum completions from same IP per day');
            
            $table->integer('cooldown_seconds')
                ->default(60)
                ->after('duration_seconds')
                ->comment('Minimum seconds between task starts for same user');
        });

        // Add enhanced tracking to task_completions
        Schema::table('task_completions', function (Blueprint $table) {
            $table->string('provider_ref')
                ->nullable()
                ->after('metadata')
                ->comment('Unique reference from provider postback for idempotency');
            
            $table->decimal('provider_payout', 10, 4)
                ->nullable()
                ->after('provider_ref')
                ->comment('Actual payout amount from provider (for analytics)');
            
            $table->string('provider')
                ->nullable()
                ->after('provider_payout')
                ->comment('Provider name (adsterra, monetag, etc.)');
            
            $table->boolean('from_postback')
                ->default(false)
                ->after('provider')
                ->comment('Whether this completion was triggered by postback');
            
            $table->timestamp('postback_received_at')
                ->nullable()
                ->after('completed_at')
                ->comment('When postback was received from provider');
            
            // Add unique index for provider reference (prevent duplicates)
            $table->unique(['provider', 'provider_ref'], 'unique_provider_ref');
            
            // Index for finding completions by provider
            $table->index('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_completions', function (Blueprint $table) {
            $table->dropUnique('unique_provider_ref');
            $table->dropIndex(['provider']);
            $table->dropColumn([
                'provider_ref',
                'provider_payout', 
                'provider',
                'from_postback',
                'postback_received_at',
            ]);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'require_postback',
                'min_payout',
                'ip_daily_limit',
                'cooldown_seconds',
            ]);
        });
    }
};
