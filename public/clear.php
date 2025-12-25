<?php
/**
 * DIRECT Cache Clear - No Laravel Boot Required
 * URL: https://skypesa.hosting.hollyn.online/clear.php
 * DELETE AFTER USE!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre style='background:#1a1a2e;color:#00ff00;padding:30px;font-family:monospace;font-size:14px;'>";
echo "╔══════════════════════════════════════════════════╗\n";
echo "║   SKYpesa DIRECT Cache Clear (No Artisan)       ║\n";
echo "╚══════════════════════════════════════════════════╝\n\n";

$root = dirname(__DIR__);
$deleted = 0;
$errors = [];

// 1. Delete bootstrap cache files (THIS IS THE KEY!)
echo "1. BOOTSTRAP CACHE FILES:\n";
$bootstrapCacheFiles = [
    '/bootstrap/cache/packages.php',
    '/bootstrap/cache/services.php',
    '/bootstrap/cache/config.php',
    '/bootstrap/cache/routes-v7.php',
    '/bootstrap/cache/events.php',
];

foreach ($bootstrapCacheFiles as $file) {
    $fullPath = $root . $file;
    if (file_exists($fullPath)) {
        if (@unlink($fullPath)) {
            echo "   ✅ DELETED: $file\n";
            $deleted++;
        } else {
            echo "   ❌ FAILED: $file (check permissions)\n";
            $errors[] = $file;
        }
    } else {
        echo "   ⚪ NOT FOUND: $file\n";
    }
}

// 2. Clear storage/framework/cache
echo "\n2. STORAGE CACHE:\n";
$storageCachePath = $root . '/storage/framework/cache/data';
if (is_dir($storageCachePath)) {
    $count = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($storageCachePath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() !== '.gitignore') {
            @unlink($file->getRealPath());
            $count++;
        }
    }
    echo "   ✅ Cleared $count cache files\n";
    $deleted += $count;
} else {
    echo "   ⚪ Storage cache directory not found\n";
}

// 3. Clear compiled views
echo "\n3. COMPILED VIEWS:\n";
$viewsPath = $root . '/storage/framework/views';
if (is_dir($viewsPath)) {
    $count = 0;
    $files = glob($viewsPath . '/*.php');
    foreach ($files as $file) {
        @unlink($file);
        $count++;
    }
    echo "   ✅ Cleared $count compiled views\n";
    $deleted += $count;
} else {
    echo "   ⚪ Views cache directory not found\n";
}

// 4. Check vendor/laravel/boost (should NOT exist on production)
echo "\n4. CHECKING FOR BOOST PACKAGE:\n";
$boostPath = $root . '/vendor/laravel/boost';
if (is_dir($boostPath)) {
    echo "   ⚠️  WARNING: vendor/laravel/boost EXISTS!\n";
    echo "   This is a dev package and should NOT be on production.\n";
    echo "   You need to upload vendor folder from production build.\n";
} else {
    echo "   ✅ GOOD: vendor/laravel/boost does not exist\n";
}

// 5. Check composer.json for boost
echo "\n5. CHECKING COMPOSER.JSON:\n";
$composerPath = $root . '/composer.json';
if (file_exists($composerPath)) {
    $composer = json_decode(file_get_contents($composerPath), true);
    if (isset($composer['require-dev']['laravel/boost'])) {
        echo "   ℹ️  laravel/boost is in require-dev (OK for production)\n";
    }
    if (isset($composer['require']['laravel/boost'])) {
        echo "   ❌ ERROR: laravel/boost is in require (should be require-dev)\n";
    }
}

// Summary
echo "\n══════════════════════════════════════════════════\n";
echo "SUMMARY:\n";
echo "   Files deleted: $deleted\n";

if (count($errors) > 0) {
    echo "   Errors: " . count($errors) . "\n";
    echo "\n   To fix permission errors, run via SSH:\n";
    echo "   chmod -R 755 bootstrap/cache\n";
    echo "   chmod -R 755 storage\n";
}

echo "\n══════════════════════════════════════════════════\n";
echo "NEXT STEPS:\n";
echo "1. Refresh the main site: https://skypesa.hosting.hollyn.online\n";
echo "2. If still error, you need to upload vendor_new.zip\n";
echo "3. DELETE this clear.php file!\n";
echo "══════════════════════════════════════════════════\n";
echo "</pre>";
