<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add video support to announcements - allowing video announcements (10-15 seconds)
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->enum('media_type', ['text', 'video'])->default('text')->after('body');
            $table->string('video_path')->nullable()->after('media_type');
            $table->integer('video_duration')->nullable()->after('video_path'); // in seconds
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'video_path', 'video_duration']);
        });
    }
};
