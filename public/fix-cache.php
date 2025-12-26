<?php
/**
 * EMERGENCY FIX - Recreate Laravel storage directories
 * Access via: https://yoursite.com/fix-cache.php
 * DELETE THIS FILE AFTER USE!
 */

echo "<h1>🚨 Emergency Cache Fix</h1>";
echo "<style>body { font-family: Arial; padding: 20px; background: #1a1a1a; color: #fff; } 
.ok { color: #10b981; } .error { color: #ef4444; } .warn { color: #f59e0b; }
pre { background: #333; padding: 10px; border-radius: 5px; }</style>";

// Detect Laravel root
$publicPath = $_SERVER['DOCUMENT_ROOT'];
$laravelRoot = null;

// Try to find Laravel root
$possibleRoots = [
    dirname($publicPath),
    dirname(dirname($publicPath)),
    $publicPath . '/..',
];

foreach ($possibleRoots as $root) {
    if (file_exists($root . '/artisan')) {
        $laravelRoot = realpath($root);
        break;
    }
}

if (!$laravelRoot) {
    echo "<p class='error'>❌ Cannot find Laravel root!</p>";
    exit;
}

echo "<p>Laravel Root: <code>" . $laravelRoot . "</code></p>";

// Directories that must exist
$directories = [
    $laravelRoot . '/storage',
    $laravelRoot . '/storage/app',
    $laravelRoot . '/storage/app/public',
    $laravelRoot . '/storage/app/public/announcements',
    $laravelRoot . '/storage/app/public/announcements/videos',
    $laravelRoot . '/storage/framework',
    $laravelRoot . '/storage/framework/cache',
    $laravelRoot . '/storage/framework/cache/data',
    $laravelRoot . '/storage/framework/sessions',
    $laravelRoot . '/storage/framework/testing',
    $laravelRoot . '/storage/framework/views',
    $laravelRoot . '/storage/logs',
    $laravelRoot . '/bootstrap/cache',
];

echo "<h2>Creating Required Directories...</h2>";

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p class='ok'>✅ Created: " . str_replace($laravelRoot, '', $dir) . "</p>";
        } else {
            echo "<p class='error'>❌ Failed: " . str_replace($laravelRoot, '', $dir) . "</p>";
        }
    } else {
        echo "<p>📁 Exists: " . str_replace($laravelRoot, '', $dir) . "</p>";
    }
}

// Create .gitignore files
$gitignoreContent = "*\n!.gitignore\n";
$gitignoreLocations = [
    $laravelRoot . '/storage/app/.gitignore',
    $laravelRoot . '/storage/framework/cache/.gitignore',
    $laravelRoot . '/storage/framework/sessions/.gitignore',
    $laravelRoot . '/storage/framework/views/.gitignore',
    $laravelRoot . '/storage/logs/.gitignore',
];

echo "<h2>Creating .gitignore files...</h2>";
foreach ($gitignoreLocations as $file) {
    if (!file_exists($file)) {
        if (file_put_contents($file, $gitignoreContent)) {
            echo "<p class='ok'>✅ Created: " . basename(dirname($file)) . "/.gitignore</p>";
        }
    }
}

// Create empty log file
$logFile = $laravelRoot . '/storage/logs/laravel.log';
if (!file_exists($logFile)) {
    touch($logFile);
    chmod($logFile, 0664);
    echo "<p class='ok'>✅ Created: laravel.log</p>";
}

// Set permissions
echo "<h2>Setting Permissions...</h2>";
$storageDir = $laravelRoot . '/storage';
$bootstrapCache = $laravelRoot . '/bootstrap/cache';

// Try to set permissions recursively
function setPerms($dir) {
    if (is_dir($dir)) {
        chmod($dir, 0755);
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    setPerms($path);
                } else {
                    chmod($path, 0644);
                }
            }
        }
    }
}

setPerms($storageDir);
setPerms($bootstrapCache);
echo "<p class='ok'>✅ Permissions set to 755/644</p>";

// Now check/fix the public/storage symlink
$publicStorageLink = $publicPath . '/storage';
$storagePath = $laravelRoot . '/storage/app/public';

echo "<h2>Checking Public Storage Symlink...</h2>";

if (file_exists($publicStorageLink)) {
    if (is_link($publicStorageLink)) {
        echo "<p class='ok'>✅ Symlink exists: " . readlink($publicStorageLink) . "</p>";
    } else {
        echo "<p class='warn'>⚠️ /storage exists but is not a symlink</p>";
        // Try to fix it
        function removeDir($dir) {
            if (is_dir($dir)) {
                $files = array_diff(scandir($dir), ['.', '..']);
                foreach ($files as $file) {
                    $path = $dir . '/' . $file;
                    is_dir($path) ? removeDir($path) : unlink($path);
                }
                return rmdir($dir);
            }
            return unlink($dir);
        }
        
        if (removeDir($publicStorageLink)) {
            if (symlink($storagePath, $publicStorageLink)) {
                echo "<p class='ok'>✅ Fixed: Created proper symlink</p>";
            }
        }
    }
} else {
    if (symlink($storagePath, $publicStorageLink)) {
        echo "<p class='ok'>✅ Created symlink: storage → " . $storagePath . "</p>";
    } else {
        echo "<p class='error'>❌ Failed to create symlink</p>";
    }
}

echo "<h2 class='ok'>✅ All Done!</h2>";
echo "<p>Try loading your site again: <a href='/' style='color: #10b981;'>Go to homepage</a></p>";
echo "<p><strong class='error'>⚠️ DELETE THIS FILE NOW!</strong></p>";
?>
