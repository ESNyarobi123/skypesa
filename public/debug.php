<?php
// Debug file to see actual error
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>PHP Info:</h3>";
echo "PHP Version: " . phpversion() . "<br>";

echo "<h3>Checking critical paths:</h3>";

// Check if vendor exists
$vendorPath = __DIR__ . '/../vendor/autoload.php';
echo "Vendor autoload exists: " . (file_exists($vendorPath) ? "YES ✓" : "NO ✗") . "<br>";

// Check if .env exists
$envPath = __DIR__ . '/../.env';
echo ".env file exists: " . (file_exists($envPath) ? "YES ✓" : "NO ✗") . "<br>";

// Check storage permissions
$storagePath = __DIR__ . '/../storage';
echo "Storage directory exists: " . (file_exists($storagePath) ? "YES ✓" : "NO ✗") . "<br>";
echo "Storage is writable: " . (is_writable($storagePath) ? "YES ✓" : "NO ✗") . "<br>";

// Check bootstrap/cache
$cachePath = __DIR__ . '/../bootstrap/cache';
echo "Bootstrap/cache exists: " . (file_exists($cachePath) ? "YES ✓" : "NO ✗") . "<br>";
echo "Bootstrap/cache is writable: " . (is_writable($cachePath) ? "YES ✓" : "NO ✗") . "<br>";

// Check storage subdirectories
$storageDirs = [
    'framework',
    'framework/cache',
    'framework/sessions',
    'framework/views',
    'logs',
];

echo "<h3>Storage subdirectories:</h3>";
foreach ($storageDirs as $dir) {
    $fullPath = $storagePath . '/' . $dir;
    $exists = is_dir($fullPath);
    $writable = $exists && is_writable($fullPath);
    echo "storage/{$dir}: " . ($exists ? "EXISTS" : "MISSING") . " / " . ($writable ? "WRITABLE ✓" : "NOT WRITABLE ✗") . "<br>";
}

// Try to load Laravel and catch errors
echo "<h3>Attempting to load Laravel:</h3>";
try {
    require $vendorPath;
    echo "Vendor autoload loaded ✓<br>";
    
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "Bootstrap loaded ✓<br>";
    
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "Kernel created ✓<br>";
    
} catch (Exception $e) {
    echo "<h3 style='color:red'>ERROR FOUND:</h3>";
    echo "<pre style='background:#fee;padding:10px;border:1px solid red'>";
    echo "Message: " . $e->getMessage() . "\n\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
} catch (Error $e) {
    echo "<h3 style='color:red'>FATAL ERROR:</h3>";
    echo "<pre style='background:#fee;padding:10px;border:1px solid red'>";
    echo "Message: " . $e->getMessage() . "\n\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}
