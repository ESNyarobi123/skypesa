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
        Schema::create('survey_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('survey_id'); // CPX survey ID
            $table->string('transaction_id')->unique(); // CPX transaction ID
            $table->enum('survey_type', ['short', 'medium', 'long'])->default('short');
            $table->integer('loi')->nullable(); // Length of interview in minutes
            $table->decimal('cpx_payout', 10, 2)->default(0); // What CPX pays us
            $table->decimal('user_reward', 10, 2)->default(0); // What we pay user
            $table->enum('status', ['pending', 'completed', 'credited', 'rejected', 'reversed'])->default('pending');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('cpx_data')->nullable(); // Store full CPX response
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('credited_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['survey_id']);
            $table->index('created_at');
        });

        // Survey settings table
        Schema::create('survey_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_completions');
        Schema::dropIfExists('survey_settings');
    }
};
