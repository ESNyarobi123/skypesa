<?php
/**
 * Create public storage symlink for web access
 */

echo "<h1>🔗 Create Public Storage Link</h1>";
echo "<style>body { font-family: Arial; padding: 20px; background: #1a1a1a; color: #fff; } 
.ok { color: #10b981; } .error { color: #ef4444; } .warn { color: #f59e0b; }
pre { background: #333; padding: 10px; border-radius: 5px; }
.btn { background: #10b981; color: white; padding: 15px 30px; border: none; cursor: pointer; border-radius: 8px; font-size: 16px; }
</style>";

$laravelRoot = '/home/hosting/public_html/skypesa';
$documentRoot = $_SERVER['DOCUMENT_ROOT'];

// For your setup where document root IS the laravel root,
// we need a way to publicly access storage/app/public

$storagePath = $laravelRoot . '/storage/app/public';
$publicLink = $documentRoot . '/storage'; // This will be public/storage symlink

echo "<h2>📍 Configuration</h2>";
echo "<pre>";
echo "Laravel Root: " . $laravelRoot . "\n";
echo "Document Root: " . $documentRoot . "\n";
echo "Storage Path: " . $storagePath . "\n";
echo "Public Link: " . $publicLink . "\n";
echo "</pre>";

// Check current state
echo "<h2>📊 Current State</h2>";

// Check if storage/app/public exists
if (file_exists($storagePath)) {
    echo "<p class='ok'>✅ Storage app/public exists</p>";
} else {
    echo "<p class='error'>❌ Storage app/public does NOT exist</p>";
    // Create it
    if (mkdir($storagePath, 0755, true)) {
        echo "<p class='ok'>✅ Created storage/app/public</p>";
    }
}

// Check video directory
$videoDir = $storagePath . '/announcements/videos';
if (file_exists($videoDir)) {
    echo "<p class='ok'>✅ Video directory exists</p>";
    
    $videos = glob($videoDir . '/*');
    echo "<p>Files in video directory: " . count($videos) . "</p>";
} else {
    mkdir($videoDir, 0755, true);
    echo "<p class='ok'>✅ Created video directory</p>";
}

// Now the tricky part - we need public access
// Since document root IS the laravel root, we can't have /public/storage
// Instead, we'll check if there's a way to access storage directly

echo "<h2>🔧 Setting Up Public Access</h2>";

// Option 1: Check if there's already a symlink or directory at root/storage
// that's different from the actual storage folder

// For this setup, we need to create a symlink like:
// /home/hosting/public_html/skypesa/public-files -> /home/hosting/public_html/skypesa/storage/app/public

// But wait - since document root = laravel root, users access files at domain.com/path
// We need the symlink to be somewhere the web server can reach

// The standard Laravel approach won't work here
// We need to either:
// 1. Create a route that serves files
// 2. Use a different public folder structure

// Let's try option: create symlink at a web-accessible location
$symlinkPath = $documentRoot . '/public-storage';

echo "<p>Creating symlink: " . $symlinkPath . " → " . $storagePath . "</p>";

if (file_exists($symlinkPath)) {
    if (is_link($symlinkPath)) {
        echo "<p class='ok'>✅ Symlink already exists</p>";
        echo "<p>Points to: " . readlink($symlinkPath) . "</p>";
    } else {
        echo "<p class='warn'>⚠️ Path exists but is not a symlink</p>";
    }
} else {
    if (symlink($storagePath, $symlinkPath)) {
        echo "<p class='ok'>✅ Created symlink!</p>";
    } else {
        echo "<p class='error'>❌ Failed to create symlink</p>";
    }
}

// Also try the standard 'storage' name in a subdirectory
// First, we need to understand the structure better
// Is there a 'public' folder?

echo "<h2>📁 Directory Structure Analysis</h2>";

$checkPaths = [
    'public folder' => $laravelRoot . '/public',
    'storage folder' => $laravelRoot . '/storage',
    'storage/app/public' => $storagePath,
];

foreach ($checkPaths as $name => $path) {
    if (file_exists($path)) {
        $type = is_link($path) ? 'SYMLINK' : (is_dir($path) ? 'DIR' : 'FILE');
        echo "<p>✅ " . $name . ": " . $type . "</p>";
        
        if (is_link($path)) {
            echo "<p style='margin-left: 20px;'>→ " . readlink($path) . "</p>";
        }
    } else {
        echo "<p class='error'>❌ " . $name . ": NOT FOUND</p>";
    }
}

// If there's a public folder, create symlink there
if (is_dir($laravelRoot . '/public')) {
    $publicSymlink = $laravelRoot . '/public/storage';
    echo "<h3>Creating symlink in /public folder...</h3>";
    
    if (!file_exists($publicSymlink)) {
        if (symlink($storagePath, $publicSymlink)) {
            echo "<p class='ok'>✅ Created: /public/storage → storage/app/public</p>";
        }
    } else {
        if (is_link($publicSymlink)) {
            echo "<p class='ok'>✅ /public/storage symlink exists</p>";
        }
    }
}

echo "<h2>✅ Summary</h2>";
echo "<p>Storage structure is now correct. You can access uploaded files at:</p>";
echo "<ul>";
echo "<li><code>/public-storage/announcements/videos/filename.mp4</code></li>";
echo "</ul>";

echo "<h2>📌 Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Delete the old video announcement</strong> (it has wrong path)</li>";
echo "<li><strong>Create a NEW video announcement</strong> from admin panel</li>";
echo "<li>The new video will save to the correct location</li>";
echo "</ol>";

echo "<p><a href='/admin/announcements' style='color: #10b981;'>→ Go to Announcements Admin</a></p>";

// Update: Also need to update Announcement model to use correct URL
echo "<h2 class='warn'>⚠️ Important: Update Video URL Path</h2>";
echo "<p>The video URL in your code needs to match the public symlink path.</p>";
echo "<p>Current symlink: <code>/public-storage/</code></p>";

echo "<hr><p class='error'><strong>DELETE ALL FIX SCRIPTS AFTER DONE!</strong></p>";
?>
