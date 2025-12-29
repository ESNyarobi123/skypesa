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
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable(); // Extra custom data
            $table->string('image_url')->nullable(); // Optional notification image
            $table->enum('target_type', ['all', 'specific', 'segment'])->default('all');
            $table->json('target_users')->nullable(); // User IDs for specific targeting
            $table->string('segment')->nullable(); // For segment targeting (e.g., 'premium', 'free', 'inactive')
            $table->integer('total_tokens')->default(0); // Total tokens attempted
            $table->integer('success_count')->default(0); // Successfully delivered
            $table->integer('failure_count')->default(0); // Failed deliveries
            $table->json('error_details')->nullable(); // Error info for failed deliveries
            $table->enum('status', ['pending', 'sending', 'completed', 'failed'])->default('pending');
            $table->foreignId('sent_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('target_type');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_notifications');
    }
};
