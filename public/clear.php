<?php
/**
 * Emergency Cache Clear Script
 * URL: https://skypesa.hosting.hollyn.online/clear.php
 * DELETE AFTER USE!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre style='background:#000;color:#0f0;padding:20px;'>";
echo "=== SKYpesa Emergency Cache Clear ===\n\n";

$root = dirname(__DIR__);

// Files to delete
$cacheFiles = [
    $root . '/bootstrap/cache/packages.php',
    $root . '/bootstrap/cache/services.php',
    $root . '/bootstrap/cache/config.php',
    $root . '/bootstrap/cache/routes-v7.php',
];

foreach ($cacheFiles as $file) {
    $name = str_replace($root, '', $file);
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "✅ Deleted: $name\n";
        } else {
            echo "❌ Failed to delete: $name\n";
        }
    } else {
        echo "⚪ Not found: $name\n";
    }
}

// Clear storage cache
$storageCachePath = $root . '/storage/framework/cache/data';
if (is_dir($storageCachePath)) {
    $files = glob($storageCachePath . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    echo "✅ Cleared storage cache\n";
}

// Clear views cache
$viewsCachePath = $root . '/storage/framework/views';
if (is_dir($viewsCachePath)) {
    $files = glob($viewsCachePath . '/*.php');
    $count = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            $count++;
        }
    }
    echo "✅ Cleared $count compiled views\n";
}

echo "\n=== DONE! ===\n";
echo "\n⚠️ NOW DELETE THIS FILE (clear.php)\n";
echo "   Then refresh: https://skypesa.hosting.hollyn.online\n";
echo "</pre>";
