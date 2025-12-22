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
        // Link Pools table (SkyBoost™, SkyLinks™, etc.)
        Schema::create('link_pools', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // e.g., "SkyBoost™"
            $table->string('slug')->unique();                // e.g., "skyboost"
            $table->text('description')->nullable();         // Pool description
            $table->string('icon')->default('zap');          // Lucide icon name
            $table->string('color')->default('#10B981');     // Theme color
            $table->decimal('reward_amount', 10, 2)->default(5); // Reward per completion
            $table->integer('duration_seconds')->default(30); // Time to view ad
            $table->integer('daily_user_limit')->nullable(); // Max tasks per user/day
            $table->integer('daily_global_limit')->nullable(); // Max tasks globally/day
            $table->integer('cooldown_seconds')->default(120); // Cooldown between tasks
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Individual links within pools
        Schema::create('pool_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_pool_id')
                  ->constrained('link_pools')
                  ->onDelete('cascade');
            $table->string('name');                          // e.g., "Adsterra Smartlink 1"
            $table->string('url', 500);                      // The actual ad URL
            $table->string('provider')->default('other');    // adsterra, monetag, etc.
            $table->integer('total_clicks')->default(0);     // All-time clicks
            $table->integer('clicks_today')->default(0);     // Today's clicks
            $table->timestamp('last_click_at')->nullable();  // Last click timestamp
            $table->integer('weight')->default(1);           // Weight for random selection
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();               // Admin notes
            $table->timestamps();
            
            $table->index(['link_pool_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pool_links');
        Schema::dropIfExists('link_pools');
    }
};
