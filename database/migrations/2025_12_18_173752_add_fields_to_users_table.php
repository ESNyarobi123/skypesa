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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->unique()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->enum('role', ['admin', 'user'])->default('user')->after('avatar');
            $table->boolean('is_active')->default(true)->after('role');
            $table->boolean('is_verified')->default(false)->after('is_active');
            $table->string('referral_code')->unique()->nullable()->after('is_verified');
            $table->foreignId('referred_by')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            $table->string('device_fingerprint')->nullable()->after('referred_by');
            $table->timestamp('last_login_at')->nullable()->after('device_fingerprint');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            
            $table->index('role');
            $table->index('is_active');
            $table->index('referral_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropColumn([
                'phone',
                'avatar',
                'role',
                'is_active',
                'is_verified',
                'referral_code',
                'referred_by',
                'device_fingerprint',
                'last_login_at',
                'last_login_ip',
            ]);
        });
    }
};
