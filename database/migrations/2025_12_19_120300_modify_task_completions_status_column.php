<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // We need to change the ENUM to include new status values
        // First, let's change it to VARCHAR to accommodate all statuses
        Schema::table('task_completions', function (Blueprint $table) {
            // Change from ENUM to VARCHAR
            $table->string('status', 20)->default('completed')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to original enum (data might be lost for new statuses)
        Schema::table('task_completions', function (Blueprint $table) {
            $table->enum('status', ['completed', 'pending', 'rejected', 'fraud'])->default('completed')->change();
        });
    }
};
