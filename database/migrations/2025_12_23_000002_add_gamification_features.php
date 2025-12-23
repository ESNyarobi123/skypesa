<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * GAMIFICATION FEATURES:
     * 1. Welcome Bonus - First task x10 reward + signup bonus
     * 2. Daily Goals - Complete X tasks, get bonus
     * 3. Leaderboard tracking
     */
    public function up(): void
    {
        // Add gamification fields to users table
        Schema::table('users', function (Blueprint $table) {
            // Welcome Bonus Tracking
            $table->boolean('received_welcome_bonus')->default(false)->after('is_active');
            $table->boolean('first_task_completed')->default(false)->after('received_welcome_bonus');
            $table->timestamp('first_task_at')->nullable()->after('first_task_completed');
            
            // Daily Goals Tracking
            $table->date('last_daily_goal_date')->nullable()->after('first_task_at');
            $table->integer('daily_goal_progress')->default(0)->after('last_daily_goal_date');
            $table->boolean('daily_goal_claimed')->default(false)->after('daily_goal_progress');
        });

        // Daily Goals Configuration Table
        Schema::create('daily_goals', function (Blueprint $table) {
            $table->id();
            $table->string('name');                              // e.g., "Complete 15 tasks"
            $table->text('description')->nullable();             // Goal description
            $table->integer('target_tasks')->default(15);        // Tasks required
            $table->decimal('bonus_amount', 10, 2)->default(50); // Bonus TZS
            $table->string('icon')->default('target');           // Lucide icon
            $table->string('color')->default('#10B981');         // Theme color
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Daily Goal Completions (History)
        Schema::create('daily_goal_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('daily_goal_id')->constrained()->onDelete('cascade');
            $table->date('completed_date');
            $table->integer('tasks_completed');
            $table->decimal('bonus_earned', 10, 2);
            $table->timestamps();
            
            $table->unique(['user_id', 'completed_date']);
            $table->index(['completed_date']);
        });

        // Leaderboard Snapshots (for performance - updated hourly/daily)
        Schema::create('leaderboard_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('period', ['daily', 'weekly', 'monthly', 'all_time']);
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('rank');
            $table->integer('tasks_completed')->default(0);
            $table->decimal('earnings', 12, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'period', 'period_start']);
            $table->index(['period', 'period_start', 'rank']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboard_entries');
        Schema::dropIfExists('daily_goal_completions');
        Schema::dropIfExists('daily_goals');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'received_welcome_bonus',
                'first_task_completed',
                'first_task_at',
                'last_daily_goal_date',
                'daily_goal_progress',
                'daily_goal_claimed',
            ]);
        });
    }
};
