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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reference')->unique(); // Unique withdrawal reference
            $table->decimal('amount', 15, 2); // Amount requested
            $table->decimal('fee', 15, 2)->default(0); // Fee charged
            $table->decimal('net_amount', 15, 2); // Amount after fee (to be paid)
            $table->string('payment_method')->default('mobile_money'); // mobile_money, bank
            $table->string('payment_number'); // Phone number or bank account
            $table->string('payment_name')->nullable(); // Account holder name
            $table->string('payment_provider')->nullable(); // M-Pesa, Tigo Pesa, etc
            $table->enum('status', ['pending', 'processing', 'approved', 'paid', 'rejected', 'cancelled'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->string('zenopay_reference')->nullable(); // ZenoPay transaction reference
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
