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
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->string('ticket_number')->unique()->after('id');
            $table->enum('category', ['general', 'task', 'withdrawal', 'subscription', 'account', 'bug', 'other'])->default('general')->after('subject');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->after('category');
            $table->text('initial_message')->nullable()->after('priority');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null')->after('status');
            $table->timestamp('resolved_at')->nullable()->after('last_message_at');
        });

        // Update status enum to include more states
        // Note: This is done after adding columns to avoid issues
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->string('status')->default('open')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn([
                'ticket_number',
                'category',
                'priority',
                'initial_message',
                'assigned_to',
                'resolved_at',
            ]);
        });
    }
};
