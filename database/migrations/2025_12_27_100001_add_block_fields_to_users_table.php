<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add blocking functionality to users table
     * - is_blocked: Whether user is blocked from using the system
     * - blocked_reason: Why user was blocked
     * - blocked_at: When user was blocked
     * - blocked_by: Admin who blocked the user (null = auto-blocked)
     * - total_flagged_clicks: Counter for suspicious clicks (auto-block at 20)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('is_suspicious');
            $table->string('blocked_reason')->nullable()->after('is_blocked');
            $table->timestamp('blocked_at')->nullable()->after('blocked_reason');
            $table->foreignId('blocked_by')->nullable()->after('blocked_at')->constrained('users')->onDelete('set null');
            $table->integer('total_flagged_clicks')->default(0)->after('blocked_by'); // Auto-block when >= 20
            
            // Index for quick blocked user queries
            $table->index('is_blocked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['blocked_by']);
            $table->dropIndex(['is_blocked']);
            $table->dropColumn(['is_blocked', 'blocked_reason', 'blocked_at', 'blocked_by', 'total_flagged_clicks']);
        });
    }
};
