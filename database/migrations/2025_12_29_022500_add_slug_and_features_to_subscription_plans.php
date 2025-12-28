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
        // Step 1: Add columns without unique constraint
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription_plans', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (!Schema::hasColumn('subscription_plans', 'features')) {
                $table->json('features')->nullable()->after('description');
            }
        });

        // Step 2: Update existing plans to have slugs
        $plans = \DB::table('subscription_plans')->get();
        foreach ($plans as $plan) {
            $slug = match(strtolower($plan->name)) {
                'free' => 'free',
                'phase1' => 'silver',
                'phase2' => 'gold',
                'premium' => 'vip',
                default => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $plan->name)),
            };
            
            \DB::table('subscription_plans')
                ->where('id', $plan->id)
                ->update(['slug' => $slug]);
        }

        // Step 3: Now add unique constraint
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'features']);
        });
    }
};
