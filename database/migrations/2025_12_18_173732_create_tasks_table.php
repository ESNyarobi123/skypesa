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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['view_ad', 'share_link', 'survey', 'other'])->default('view_ad');
            $table->string('url'); // Monetag smartlink or Adsterra direct link
            $table->string('provider')->default('monetag'); // monetag, adsterra, custom
            $table->integer('duration_seconds')->default(30); // Time user must view the ad
            $table->decimal('reward_override', 10, 2)->nullable(); // Override plan reward if set
            $table->integer('daily_limit')->nullable(); // Max times per day per user
            $table->integer('total_limit')->nullable(); // Total completions allowed
            $table->integer('completions_count')->default(0); // Track total completions
            $table->string('thumbnail')->nullable(); // Task image
            $table->string('icon')->nullable();
            $table->json('requirements')->nullable(); // Any special requirements
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'type']);
            $table->index('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
