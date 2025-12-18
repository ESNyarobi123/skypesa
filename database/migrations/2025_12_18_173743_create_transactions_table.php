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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->string('reference')->unique(); // Unique transaction reference
            $table->enum('type', ['credit', 'debit']);
            $table->enum('category', [
                'task_reward',      // Earned from completing task
                'withdrawal',       // Withdrawal debit
                'withdrawal_fee',   // Withdrawal fee debit
                'deposit',          // Deposit via ZenoPay
                'subscription',     // Subscription payment
                'bonus',            // Admin bonus
                'refund',           // Refund
                'adjustment',       // Manual adjustment
            ]);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('description')->nullable();
            $table->morphs('transactionable'); // Polymorphic relation to task_completion, withdrawal, etc
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'type', 'created_at']);
            $table->index(['wallet_id', 'created_at']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
