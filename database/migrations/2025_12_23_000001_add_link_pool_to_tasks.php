<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This adds link_pool_id to tasks table.
     * When a task has a link_pool_id, the system will pick a RANDOM link
     * from that pool when user starts the task.
     * 
     * SkyBoost™ -> pulls random from SkyBoost pool
     * SkyLinks™ -> pulls random from SkyLinks pool
     */
    public function up(): void
    {
        // Add link_pool_id to tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('link_pool_id')
                  ->nullable()
                  ->after('url')
                  ->constrained('link_pools')
                  ->onDelete('set null');
        });

        // Add pool_link_id to task_completions to track which specific link was used
        Schema::table('task_completions', function (Blueprint $table) {
            $table->foreignId('pool_link_id')
                  ->nullable()
                  ->after('task_id')
                  ->constrained('pool_links')
                  ->onDelete('set null');
            
            $table->string('used_url', 500)->nullable()->after('pool_link_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_completions', function (Blueprint $table) {
            $table->dropForeign(['pool_link_id']);
            $table->dropColumn(['pool_link_id', 'used_url']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['link_pool_id']);
            $table->dropColumn('link_pool_id');
        });
    }
};
