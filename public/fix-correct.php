<?php
/**
 * CORRECT FIX - For Laravel in subdirectory
 * Your structure: public_html/skypesa/ is the Laravel root
 */

echo "<h1>🔧 Correct Laravel Fix</h1>";
echo "<style>body { font-family: Arial; padding: 20px; background: #1a1a1a; color: #fff; } 
.ok { color: #10b981; } .error { color: #ef4444; } .warn { color: #f59e0b; }
pre { background: #333; padding: 10px; border-radius: 5px; }</style>";

// The CORRECT Laravel root based on your error message
$laravelRoot = '/home/hosting/public_html/skypesa';
$publicPath = $_SERVER['DOCUMENT_ROOT']; // This might be the same as laravelRoot

echo "<h2>📍 Path Information</h2>";
echo "<pre>";
echo "Document Root: " . $publicPath . "\n";
echo "Laravel Root: " . $laravelRoot . "\n";
echo "</pre>";

// Verify this is correct
if (!file_exists($laravelRoot . '/artisan')) {
    echo "<p class='error'>❌ artisan not found at: " . $laravelRoot . "</p>";
    
    // Try to find it
    $searchPaths = [
        $publicPath,
        dirname($publicPath),
        $publicPath . '/skypesa',
    ];
    
    foreach ($searchPaths as $path) {
        if (file_exists($path . '/artisan')) {
            $laravelRoot = $path;
            echo "<p class='ok'>✅ Found Laravel at: " . $path . "</p>";
            break;
        }
    }
} else {
    echo "<p class='ok'>✅ Laravel root confirmed: " . $laravelRoot . "</p>";
}

// Check if vendor exists
if (file_exists($laravelRoot . '/vendor')) {
    echo "<p class='ok'>✅ Vendor directory exists</p>";
} else {
    echo "<p class='error'>❌ Vendor directory missing!</p>";
}

// Create ALL required directories
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

echo "<h2>📁 Creating Directories in CORRECT location...</h2>";

foreach ($directories as $dir) {
    $shortPath = str_replace($laravelRoot, '', $dir);
    if (!file_exists($dir)) {
        if (@mkdir($dir, 0755, true)) {
            echo "<p class='ok'>✅ Created: " . $shortPath . "</p>";
        } else {
            echo "<p class='error'>❌ Failed to create: " . $shortPath . "</p>";
        }
    } else {
        echo "<p>📁 Already exists: " . $shortPath . "</p>";
    }
}

// Create .gitignore files  
echo "<h2>📝 Creating .gitignore files...</h2>";
$gitignore = "*\n!.gitignore\n";
$gitignoreFiles = [
    $laravelRoot . '/storage/app/.gitignore' => $gitignore,
    $laravelRoot . '/storage/framework/cache/.gitignore' => $gitignore,
    $laravelRoot . '/storage/framework/cache/data/.gitignore' => $gitignore,
    $laravelRoot . '/storage/framework/sessions/.gitignore' => $gitignore,
    $laravelRoot . '/storage/framework/views/.gitignore' => $gitignore,
    $laravelRoot . '/storage/logs/.gitignore' => "*\n!.gitignore\n!laravel.log\n",
];

foreach ($gitignoreFiles as $file => $content) {
    $dir = basename(dirname($file));
    if (!file_exists($file)) {
        if (@file_put_contents($file, $content)) {
            echo "<p class='ok'>✅ Created: " . $dir . "/.gitignore</p>";
        }
    }
}

// Create empty log file
$logFile = $laravelRoot . '/storage/logs/laravel.log';
if (!file_exists($logFile)) {
    @touch($logFile);
    @chmod($logFile, 0664);
    echo "<p class='ok'>✅ Created: laravel.log</p>";
}

// Set permissions
echo "<h2>🔐 Setting Permissions...</h2>";

function chmod_r($path, $dirPerm = 0755, $filePerm = 0644) {
    if (is_dir($path)) {
        @chmod($path, $dirPerm);
        $items = glob($path . '/*');
        foreach ($items as $item) {
            chmod_r($item, $dirPerm, $filePerm);
        }
    } else {
        @chmod($path, $filePerm);
    }
}

chmod_r($laravelRoot . '/storage');
chmod_r($laravelRoot . '/bootstrap/cache');
echo "<p class='ok'>✅ Permissions set</p>";

// Handle public/storage symlink - determine where public folder is
echo "<h2>🔗 Setting up Storage Symlink...</h2>";

// Check if public folder exists as subdirectory or if skypesa IS the public folder
$publicStorageLink = null;
$storagePath = $laravelRoot . '/storage/app/public';

if (is_dir($laravelRoot . '/public')) {
    // Standard Laravel structure - public is a subdirectory
    $publicStorageLink = $laravelRoot . '/public/storage';
    echo "<p>Using: " . $laravelRoot . "/public/storage</p>";
} else {
    // Document root IS the laravel root (public files moved to root)
    $publicStorageLink = $laravelRoot . '/storage-link';
    echo "<p>Non-standard setup detected. Public files appear to be in Laravel root.</p>";
    
    // Actually, if document root is skypesa, we need symlink there
    if ($publicPath == $laravelRoot) {
        $publicStorageLink = $laravelRoot . '/storage';
        // But wait, storage already exists as directory!
        // We need a different approach - create symlink with different name
        $publicStorageLink = $laravelRoot . '/public-storage';
    }
}

// For your case, where public files are in Laravel root
// We'll create the symlink at a path that won't conflict
$publicStorageLink = $publicPath . '/uploads';
echo "<p>Creating symlink at: " . $publicStorageLink . "</p>";

if (file_exists($publicStorageLink)) {
    if (is_link($publicStorageLink)) {
        echo "<p class='ok'>✅ Symlink already exists</p>";
    } else {
        echo "<p class='warn'>⚠️ Path exists but is not a symlink</p>";
    }
} else {
    if (@symlink($storagePath, $publicStorageLink)) {
        echo "<p class='ok'>✅ Created symlink: uploads → storage/app/public</p>";
    } else {
        echo "<p class='warn'>⚠️ Could not create symlink (may need SSH)</p>";
    }
}

// Clear any cached config
echo "<h2>🧹 Clearing Cache...</h2>";
$cacheFiles = glob($laravelRoot . '/bootstrap/cache/*.php');
foreach ($cacheFiles as $cacheFile) {
    if (@unlink($cacheFile)) {
        echo "<p class='ok'>✅ Deleted: " . basename($cacheFile) . "</p>";
    }
}

echo "<h2 class='ok'>✅ DONE!</h2>";
echo "<p><strong>Now try your site: </strong><a href='/' style='color: #10b981;'>Go to homepage</a></p>";

echo "<hr><h3>📋 Summary of what was done:</h3>";
echo "<ul>";
echo "<li>Created storage directories in: " . $laravelRoot . "/storage/</li>";
echo "<li>Created framework subdirectories (cache, sessions, views)</li>";
echo "<li>Set proper permissions (755 for dirs, 644 for files)</li>";
echo "<li>Cleared bootstrap cache</li>";
echo "</ul>";

echo "<p class='error'><strong>⚠️ DELETE THIS FILE IMMEDIATELY!</strong></p>";
?>
