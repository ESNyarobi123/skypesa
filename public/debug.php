<?php
/**
 * SKYpesa Debug Script
 * URL: https://skypesa.hosting.hollyn.online/debug.php
 * DELETE IMMEDIATELY AFTER USE!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre style='background:#1a1a2e;color:#0f0;padding:20px;font-family:monospace;'>";
echo "╔══════════════════════════════════════╗\n";
echo "║     SKYpesa Server Debug Tool        ║\n";
echo "╚══════════════════════════════════════╝\n\n";

// 1. PHP Version
echo "1. PHP VERSION: " . phpversion() . "\n";

// 2. Check if vendor folder exists
$vendorPath = dirname(__DIR__) . '/vendor';
echo "\n2. VENDOR FOLDER:\n";
echo "   Path: $vendorPath\n";
echo "   Exists: " . (is_dir($vendorPath) ? "✅ YES" : "❌ NO") . "\n";

// 3. Check autoload
$autoloadPath = $vendorPath . '/autoload.php';
echo "\n3. AUTOLOAD FILE:\n";
echo "   Path: $autoloadPath\n";
echo "   Exists: " . (file_exists($autoloadPath) ? "✅ YES" : "❌ NO") . "\n";

// 4. Check Sanctum
$sanctumPath = $vendorPath . '/laravel/sanctum';
echo "\n4. LARAVEL SANCTUM:\n";
echo "   Path: $sanctumPath\n";
echo "   Exists: " . (is_dir($sanctumPath) ? "✅ YES (GOOD!)" : "❌ NO - NEED TO UPLOAD vendor_new.zip!") . "\n";

// 5. Check key files
echo "\n5. KEY FILES CHECK:\n";
$files = [
    'config/sanctum.php' => dirname(__DIR__) . '/config/sanctum.php',
    'app/Models/User.php' => dirname(__DIR__) . '/app/Models/User.php',
    '.env' => dirname(__DIR__) . '/.env',
    'bootstrap/cache' => dirname(__DIR__) . '/bootstrap/cache',
    'storage/framework/cache' => dirname(__DIR__) . '/storage/framework/cache',
];

foreach ($files as $name => $path) {
    $exists = file_exists($path) || is_dir($path);
    echo "   $name: " . ($exists ? "✅ OK" : "❌ MISSING") . "\n";
}

// 6. Check permissions
echo "\n6. FOLDER PERMISSIONS:\n";
$folders = [
    'storage' => dirname(__DIR__) . '/storage',
    'storage/logs' => dirname(__DIR__) . '/storage/logs',
    'bootstrap/cache' => dirname(__DIR__) . '/bootstrap/cache',
];

foreach ($folders as $name => $path) {
    if (is_dir($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $writable = is_writable($path);
        echo "   $name: $perms " . ($writable ? "✅ Writable" : "❌ NOT Writable") . "\n";
    } else {
        echo "   $name: ❌ NOT FOUND\n";
    }
}

// 7. Try to load Laravel and catch error
echo "\n7. LARAVEL BOOTSTRAP TEST:\n";
try {
    if (file_exists($autoloadPath)) {
        require $autoloadPath;
        echo "   Autoload: ✅ Loaded\n";
        
        $app = require_once dirname(__DIR__) . '/bootstrap/app.php';
        echo "   App: ✅ Loaded\n";
    } else {
        echo "   ❌ Cannot test - autoload missing\n";
    }
} catch (Throwable $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// 8. Show latest error log
echo "\n8. LATEST ERROR LOG:\n";
$logFile = dirname(__DIR__) . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -20);
    echo "   Last 20 lines:\n";
    foreach ($lastLines as $line) {
        echo "   " . substr($line, 0, 100) . "\n";
    }
} else {
    echo "   ❌ Log file not found\n";
}

echo "\n═══════════════════════════════════════\n";
echo "⚠️  DELETE THIS FILE AFTER DEBUGGING!\n";
echo "═══════════════════════════════════════\n";
echo "</pre>";
