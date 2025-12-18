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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('total_earned', 15, 2)->default(0); // Lifetime earnings
            $table->decimal('total_withdrawn', 15, 2)->default(0); // Lifetime withdrawals
            $table->decimal('pending_withdrawal', 15, 2)->default(0); // Currently pending
            $table->boolean('is_locked')->default(false); // Lock wallet for fraud
            $table->text('lock_reason')->nullable();
            $table->timestamps();
            
            $table->index('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
