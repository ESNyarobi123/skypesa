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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // free, phase1, phase2, premium
            $table->string('display_name'); // Free, Phase 1, Phase 2, Premium
            $table->string('description')->nullable();
            $table->decimal('price', 12, 2)->default(0); // Price in TZS
            $table->integer('duration_days')->nullable(); // null = forever (for free plan)
            $table->integer('daily_task_limit')->nullable(); // null = unlimited
            $table->decimal('reward_per_task', 10, 2)->default(50); // TZS per task
            $table->decimal('min_withdrawal', 12, 2)->default(10000); // Minimum withdrawal amount
            $table->decimal('withdrawal_fee_percent', 5, 2)->default(20); // Withdrawal fee percentage
            $table->integer('processing_days')->default(7); // Days to process withdrawal
            $table->string('badge_color')->default('#10b981'); // For UI display
            $table->string('icon')->nullable(); // Icon for the plan
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
