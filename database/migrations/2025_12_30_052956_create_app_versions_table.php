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
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version_code');
            $table->string('version_name')->nullable();
            $table->string('apk_path');
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->json('screenshots')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('force_update')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
