<?php
/**
 * Cache Refresh Script
 * Tembelea: https://skypesa.hosting.hollyn.online/refresh-cache.php
 * DELETE hii file baada ya kuitumia!
 */

// Security check - Only allow if accessed with secret key
$secret = 'skypesa_2025_refresh';
if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    die('Access denied. Use: ?key=' . $secret);
}

// Change to Laravel root
chdir(dirname(__DIR__));

echo "<pre>";
echo "=== SKYpesa Cache Refresh ===\n\n";

// Clear config cache
echo "1. Clearing config cache...\n";
echo shell_exec('php artisan config:clear 2>&1');

// Clear application cache
echo "\n2. Clearing application cache...\n";
echo shell_exec('php artisan cache:clear 2>&1');

// Clear route cache
echo "\n3. Clearing route cache...\n";
echo shell_exec('php artisan route:clear 2>&1');

// Clear view cache
echo "\n4. Clearing view cache...\n";
echo shell_exec('php artisan view:clear 2>&1');

// Clear compiled classes
echo "\n5. Clearing compiled classes...\n";
echo shell_exec('php artisan clear-compiled 2>&1');

// Optimize
echo "\n6. Optimizing...\n";
echo shell_exec('php artisan optimize:clear 2>&1');

echo "\n=== DONE! ===\n";
echo "\n⚠️ DELETE THIS FILE NOW for security!\n";
echo "</pre>";
