<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change type from enum to string to allow more task types
     */
    public function up(): void
    {
        // Drop the column and recreate as string
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('type_new')->default('view_ad')->after('description');
        });

        // Copy data
        DB::statement('UPDATE tasks SET type_new = type');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('type_new', 'type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to enum if needed
        Schema::table('tasks', function (Blueprint $table) {
            // Keep as string for simplicity
        });
    }
};
