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
        Schema::table('task_completions', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('task_id');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->boolean('is_locked')->default(false)->after('status');
            $table->integer('required_duration')->nullable()->after('duration_spent');
            $table->string('lock_token', 64)->nullable()->after('is_locked');
        });

        // Add index for quick lookups
        Schema::table('task_completions', function (Blueprint $table) {
            $table->index(['user_id', 'is_locked', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_completions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_locked', 'status']);
            $table->dropColumn(['started_at', 'completed_at', 'is_locked', 'required_duration', 'lock_token']);
        });
    }
};
