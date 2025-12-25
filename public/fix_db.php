<?php
/**
 * DATABASE FIXER - Run this once!
 * URL: https://skypesa.hosting.hollyn.online/fix_db.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre style='background:#000;color:#0f0;padding:20px;font-size:16px;'>";
echo "=== SKYpesa Database Fixer ===\n\n";

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

// Bootstrap Laravel properly
$app = require_once $root . '/bootstrap/app.php';

// IMPORTANT: Boot the Kernel to load Facades (DB, Schema, etc.)
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

try {
    // 1. Test Connection
    echo "1. Testing Database Connection...\n";
    $pdo = DB::connection()->getPdo();
    echo "   ✅ Connected to database: " . DB::connection()->getDatabaseName() . "\n";

    // 2. Create Table
    echo "\n2. Creating 'personal_access_tokens' table...\n";
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
        echo "   ✅ SUCCESS: Table created!\n";
    } else {
        echo "   ℹ️  Table already exists (Skipped)\n";
    }

    echo "\n=== DONE! Jaribu Login Sasa ===\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString();
}
echo "</pre>";
