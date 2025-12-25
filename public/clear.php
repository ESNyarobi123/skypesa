<?php
/**
 * DATABASE Migration Script - Create Sanctum Table
 * URL: https://skypesa.hosting.hollyn.online/clear.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre style='background:#1a1a2e;color:#00ff00;padding:30px;font-family:monospace;'>";
echo "╔══════════════════════════════════════════════════╗\n";
echo "║      SKYpesa Database Fix (Sanctum Table)       ║\n";
echo "╚══════════════════════════════════════════════════╝\n\n";

$root = dirname(__DIR__);

// Load Laravel
require $root . '/vendor/autoload.php';
$app = require_once $root . '/bootstrap/app.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

try {
    echo "1. Checking connection...\n";
    DB::connection()->getPdo();
    echo "   ✅ Connected to: " . DB::connection()->getDatabaseName() . "\n\n";

    echo "2. Creating 'personal_access_tokens' table...\n";
    
    if (!Schema::hasTable('personal_access_tokens')) {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        echo "   ✅ Table created successfully!\n";
    } else {
        echo "   ⚪ Table already exists.\n";
    }

    echo "\n3. Clearing cache again...\n";
    @unlink($root . '/bootstrap/cache/config.php');
    @unlink($root . '/bootstrap/cache/packages.php');
    @unlink($root . '/bootstrap/cache/services.php');
    echo "   ✅ Cache files removed.\n";

    echo "\n🚀 FIX COMPLETE! Jaribu login sasa hivi.\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n══════════════════════════════════════════════════\n";
echo "⚠️  DELETE THIS FILE IMMEDIATELY!\n";
echo "══════════════════════════════════════════════════\n";
echo "</pre>";
