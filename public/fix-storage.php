<?php
/**
 * FIX ALL - Storage, Symlinks, Directories, and APP_URL
 * Access via: https://skypesa.site/fix-storage.php
 * DELETE THIS FILE AFTER USE!
 */

echo "<h1>🔧 Fixing Video Storage Setup</h1>";
echo "<style>body { font-family: Arial; padding: 20px; background: #1a1a1a; color: #fff; } 
.ok { color: #10b981; } .error { color: #ef4444; } .warn { color: #f59e0b; }
pre { background: #333; padding: 10px; border-radius: 5px; overflow-x: auto; }
.btn { background: #10b981; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px; margin: 5px; }
.btn:hover { background: #059669; }
.btn-danger { background: #ef4444; }
.btn-danger:hover { background: #dc2626; }
</style>";

// Detect paths
$publicPath = $_SERVER['DOCUMENT_ROOT']; // /home/hosting/public_html/skypesa
$laravelRoot = dirname($publicPath); // /home/hosting/public_html (assuming public folder renamed to skypesa)

// Check if this is correct Laravel structure
if (file_exists($laravelRoot . '/artisan')) {
    // Standard setup - public folder is document root
    $storagePath = $laravelRoot . '/storage/app/public';
} elseif (file_exists($publicPath . '/../artisan')) {
    // Alternative
    $storagePath = realpath($publicPath . '/../storage/app/public');
    $laravelRoot = realpath($publicPath . '/..');
} else {
    // Try to find artisan
    $possibleRoots = [
        dirname($publicPath),
        dirname(dirname($publicPath)),
        $publicPath . '/..',
    ];
    
    foreach ($possibleRoots as $root) {
        if (file_exists($root . '/artisan')) {
            $laravelRoot = realpath($root);
            $storagePath = $laravelRoot . '/storage/app/public';
            break;
        }
    }
}

$storageLink = $publicPath . '/storage';
$videoDir = $storagePath . '/announcements/videos';
$envFile = $laravelRoot . '/.env';

echo "<h2>📍 Detected Paths</h2>";
echo "<pre>";
echo "Public (Document Root): " . $publicPath . "\n";
echo "Laravel Root: " . $laravelRoot . "\n";
echo "Storage App Public: " . $storagePath . "\n";
echo "Storage Symlink Target: " . $storageLink . "\n";
echo "Video Directory: " . $videoDir . "\n";
echo "ENV File: " . $envFile . "\n";
echo "</pre>";

// Check if artisan exists
echo "<p>Artisan exists: ";
if (file_exists($laravelRoot . '/artisan')) {
    echo "<span class='ok'>✅ YES - Laravel root found correctly</span>";
} else {
    echo "<span class='error'>❌ NO - Cannot find Laravel installation</span>";
    echo "<br>Looking in: " . $laravelRoot;
    exit;
}
echo "</p>";

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'fix_all') {
    echo "<h2>🔨 Fixing Everything...</h2>";
    
    // 1. Create storage/app/public if not exists
    echo "<p>1. Creating storage directories...</p>";
    if (!file_exists($storagePath)) {
        if (mkdir($storagePath, 0755, true)) {
            echo "<span class='ok'>✅ Created: " . $storagePath . "</span><br>";
        } else {
            echo "<span class='error'>❌ Failed to create storage path</span><br>";
        }
    } else {
        echo "<span class='ok'>✅ Storage path already exists</span><br>";
    }
    
    // 2. Create video directory
    echo "<p>2. Creating video directory...</p>";
    if (!file_exists($videoDir)) {
        if (mkdir($videoDir, 0755, true)) {
            echo "<span class='ok'>✅ Created: " . $videoDir . "</span><br>";
        } else {
            echo "<span class='error'>❌ Failed to create video directory</span><br>";
        }
    } else {
        echo "<span class='ok'>✅ Video directory already exists</span><br>";
    }
    
    // 3. Handle storage symlink
    echo "<p>3. Setting up storage symlink...</p>";
    if (file_exists($storageLink)) {
        if (is_link($storageLink)) {
            echo "<span class='ok'>✅ Symlink already exists correctly</span><br>";
        } else {
            // It's a directory, not a symlink - need to remove it
            echo "<span class='warn'>⚠️ Storage exists as directory, removing...</span><br>";
            
            // Remove directory recursively
            function removeDir($dir) {
                if (is_dir($dir)) {
                    $files = array_diff(scandir($dir), array('.', '..'));
                    foreach ($files as $file) {
                        $path = $dir . '/' . $file;
                        is_dir($path) ? removeDir($path) : unlink($path);
                    }
                    return rmdir($dir);
                }
                return false;
            }
            
            if (removeDir($storageLink)) {
                echo "<span class='ok'>✅ Removed old storage directory</span><br>";
                
                // Create symlink
                if (symlink($storagePath, $storageLink)) {
                    echo "<span class='ok'>✅ Created symlink: " . $storageLink . " → " . $storagePath . "</span><br>";
                } else {
                    echo "<span class='error'>❌ Failed to create symlink</span><br>";
                }
            } else {
                echo "<span class='error'>❌ Failed to remove old storage directory</span><br>";
                echo "Try manually: rm -rf " . $storageLink . "<br>";
            }
        }
    } else {
        // Create fresh symlink
        if (symlink($storagePath, $storageLink)) {
            echo "<span class='ok'>✅ Created symlink: " . $storageLink . " → " . $storagePath . "</span><br>";
        } else {
            echo "<span class='error'>❌ Failed to create symlink</span><br>";
        }
    }
    
    // 4. Fix APP_URL in .env
    echo "<p>4. Fixing APP_URL in .env...</p>";
    if (file_exists($envFile)) {
        $env = file_get_contents($envFile);
        
        // Detect the domain
        $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'skypesa.site';
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $correctUrl = $protocol . '://' . $domain;
        
        // Replace APP_URL
        $env = preg_replace('/APP_URL=.*/', 'APP_URL=' . $correctUrl, $env);
        
        if (file_put_contents($envFile, $env)) {
            echo "<span class='ok'>✅ Updated APP_URL to: " . $correctUrl . "</span><br>";
        } else {
            echo "<span class='error'>❌ Failed to update .env (check permissions)</span><br>";
            echo "Manually set: APP_URL=" . $correctUrl . "<br>";
        }
    } else {
        echo "<span class='error'>❌ .env file not found at: " . $envFile . "</span><br>";
    }
    
    // 5. Clear config cache
    echo "<p>5. Clearing cache...</p>";
    $cacheFiles = [
        $laravelRoot . '/bootstrap/cache/config.php',
        $laravelRoot . '/bootstrap/cache/routes.php',
        $laravelRoot . '/bootstrap/cache/services.php',
    ];
    
    foreach ($cacheFiles as $cacheFile) {
        if (file_exists($cacheFile)) {
            if (unlink($cacheFile)) {
                echo "<span class='ok'>✅ Deleted: " . basename($cacheFile) . "</span><br>";
            }
        }
    }
    
    echo "<h2 class='ok'>✅ All Done!</h2>";
    echo "<p>Now test by uploading a video announcement from admin panel.</p>";
    echo "<p><a href='storage-check.php'>→ Run storage check again</a></p>";
    echo "<p><strong>⚠️ DELETE THIS FILE NOW!</strong></p>";
    
} else {
    // Show current status and fix button
    echo "<h2>📊 Current Status</h2>";
    
    $issues = [];
    
    // Check storage path
    echo "<p>Storage App Public: ";
    if (file_exists($storagePath)) {
        echo "<span class='ok'>✅ EXISTS</span>";
    } else {
        echo "<span class='error'>❌ NOT FOUND</span>";
        $issues[] = "Create storage directory";
    }
    echo "</p>";
    
    // Check symlink
    echo "<p>Public Storage Link: ";
    if (file_exists($storageLink)) {
        if (is_link($storageLink)) {
            echo "<span class='ok'>✅ SYMLINK OK</span>";
        } else {
            echo "<span class='error'>❌ EXISTS AS DIRECTORY (not symlink)</span>";
            $issues[] = "Fix storage symlink";
        }
    } else {
        echo "<span class='error'>❌ NOT FOUND</span>";
        $issues[] = "Create storage symlink";
    }
    echo "</p>";
    
    // Check video dir
    echo "<p>Video Directory: ";
    if (file_exists($videoDir)) {
        echo "<span class='ok'>✅ EXISTS</span>";
    } else {
        echo "<span class='error'>❌ NOT FOUND</span>";
        $issues[] = "Create video directory";
    }
    echo "</p>";
    
    // Check APP_URL
    echo "<p>APP_URL: ";
    if (file_exists($envFile)) {
        $env = file_get_contents($envFile);
        preg_match('/APP_URL=(.*)/', $env, $matches);
        $appUrl = isset($matches[1]) ? trim($matches[1]) : 'Not set';
        
        if (strpos($appUrl, 'localhost') !== false || strpos($appUrl, '127.0.0.1') !== false) {
            echo "<span class='error'>❌ " . $appUrl . " (needs to be your domain)</span>";
            $issues[] = "Fix APP_URL";
        } else {
            echo "<span class='ok'>✅ " . $appUrl . "</span>";
        }
    }
    echo "</p>";
    
    if (count($issues) > 0) {
        echo "<h3 class='warn'>⚠️ Issues Found: " . count($issues) . "</h3>";
        echo "<ul>";
        foreach ($issues as $issue) {
            echo "<li>" . $issue . "</li>";
        }
        echo "</ul>";
        
        echo "<form method='get'>";
        echo "<input type='hidden' name='action' value='fix_all'>";
        echo "<button type='submit' class='btn'>🔧 FIX ALL ISSUES</button>";
        echo "</form>";
    } else {
        echo "<h3 class='ok'>✅ Everything looks good!</h3>";
    }
    
    echo "<hr><p><strong>⚠️ DELETE THIS FILE AFTER USE!</strong></p>";
}
?>
