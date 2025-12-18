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
        Schema::create('task_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->decimal('reward_earned', 10, 2);
            $table->integer('duration_spent')->default(0); // Actual seconds spent
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_fingerprint')->nullable();
            $table->json('metadata')->nullable(); // Additional tracking data
            $table->enum('status', ['completed', 'pending', 'rejected', 'fraud'])->default('completed');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'task_id', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_completions');
    }
};
