<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table tracks suspicious clicks/taps on webview
     * When user clicks on "verify if you are human" area
     */
    public function up(): void
    {
        Schema::create('user_click_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('task_completion_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('click_count')->default(0); // Number of clicks on screen
            $table->string('ip_address')->nullable();
            $table->string('device_info')->nullable();
            $table->json('click_coordinates')->nullable(); // Store x,y coordinates
            $table->text('notes')->nullable();
            $table->boolean('is_reviewed')->default(false); // Admin reviewed this flag
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['user_id', 'created_at']);
            $table->index(['is_reviewed', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_click_flags');
    }
};
