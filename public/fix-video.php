<?php
/**
 * FIX VIDEO PATH - Check and fix storage symlink for videos
 */

echo "<h1>🎬 Video Path Fixer</h1>";
echo "<style>body { font-family: Arial; padding: 20px; background: #1a1a1a; color: #fff; } 
.ok { color: #10b981; } .error { color: #ef4444; } .warn { color: #f59e0b; }
pre { background: #333; padding: 10px; border-radius: 5px; overflow-x: auto; }
.btn { background: #10b981; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px; }
</style>";

$laravelRoot = '/home/hosting/public_html/skypesa';
$documentRoot = $_SERVER['DOCUMENT_ROOT'];

echo "<h2>📍 Paths</h2>";
echo "<pre>";
echo "Document Root: " . $documentRoot . "\n";
echo "Laravel Root: " . $laravelRoot . "\n";
echo "</pre>";

// Check BOTH possible video locations
$locations = [
    'New (correct)' => $laravelRoot . '/storage/app/public/announcements/videos',
    'Old (wrong)' => '/home/hosting/public_html/storage/app/public/announcements/videos',
];

echo "<h2>📁 Looking for video files...</h2>";

$foundVideos = [];
foreach ($locations as $name => $path) {
    echo "<h3>" . $name . ": " . $path . "</h3>";
    
    if (file_exists($path)) {
        echo "<p class='ok'>✅ Directory exists</p>";
        
        $videos = glob($path . '/*.mp4');
        if (count($videos) > 0) {
            echo "<p class='ok'>🎬 Found " . count($videos) . " video(s):</p>";
            echo "<ul>";
            foreach ($videos as $video) {
                $size = round(filesize($video) / 1024, 2);
                echo "<li>" . basename($video) . " (" . $size . " KB)</li>";
                $foundVideos[$name] = $video;
            }
            echo "</ul>";
        } else {
            echo "<p class='warn'>⚠️ No videos in this directory</p>";
        }
    } else {
        echo "<p class='error'>❌ Directory does not exist</p>";
    }
}

// Check current storage symlink
echo "<h2>🔗 Storage Symlink Status</h2>";

$storageLink = $laravelRoot . '/storage';
echo "<p>Checking: " . $storageLink . "</p>";

if (file_exists($storageLink)) {
    if (is_link($storageLink)) {
        $target = readlink($storageLink);
        echo "<p class='warn'>⚠️ /storage is a SYMLINK pointing to: " . $target . "</p>";
        echo "<p class='error'>This is WRONG! /storage should be a real directory, not a symlink!</p>";
        
        // This is the problem! The storage folder itself was turned into a symlink
        // when it should be a real folder containing app, framework, logs
    } else {
        echo "<p class='ok'>✅ /storage is a real directory (correct)</p>";
    }
}

// The symlink should be at public/storage or just a way to access storage/app/public
echo "<h2>🔧 What needs to happen:</h2>";
echo "<p>For videos to work, we need:</p>";
echo "<ol>";
echo "<li><code>/home/hosting/public_html/skypesa/storage/</code> = REAL directory</li>";
echo "<li><code>/home/hosting/public_html/skypesa/storage/app/public/announcements/videos/</code> = where videos go</li>";
echo "<li>A PUBLIC URL that can access these files</li>";
echo "</ol>";

// Check if the storage symlink is wrong
if (is_link($storageLink)) {
    echo "<h2 class='error'>🚨 PROBLEM FOUND!</h2>";
    echo "<p>Your <code>/storage</code> folder was converted to a symlink. This breaks Laravel!</p>";
    
    if (isset($_GET['fix'])) {
        echo "<h3>Attempting fix...</h3>";
        
        $target = readlink($storageLink);
        
        // Remove the wrong symlink
        if (unlink($storageLink)) {
            echo "<p class='ok'>✅ Removed wrong symlink</p>";
            
            // Create proper storage directory
            if (mkdir($storageLink, 0755)) {
                echo "<p class='ok'>✅ Created storage directory</p>";
                
                // Create subdirectories
                $dirs = ['app', 'app/public', 'app/public/announcements', 'app/public/announcements/videos',
                         'framework', 'framework/cache', 'framework/cache/data', 
                         'framework/sessions', 'framework/testing', 'framework/views', 'logs'];
                
                foreach ($dirs as $dir) {
                    $fullPath = $storageLink . '/' . $dir;
                    if (!file_exists($fullPath)) {
                        mkdir($fullPath, 0755, true);
                        echo "<p class='ok'>✅ Created: /storage/" . $dir . "</p>";
                    }
                }
                
                // Copy videos from old location if they exist
                $oldVideoPath = '/home/hosting/public_html/storage/app/public/announcements/videos';
                if (file_exists($oldVideoPath)) {
                    $oldVideos = glob($oldVideoPath . '/*.mp4');
                    foreach ($oldVideos as $oldVideo) {
                        $newVideo = $storageLink . '/app/public/announcements/videos/' . basename($oldVideo);
                        if (copy($oldVideo, $newVideo)) {
                            echo "<p class='ok'>✅ Copied video: " . basename($oldVideo) . "</p>";
                        }
                    }
                }
                
                // Create log file
                touch($storageLink . '/logs/laravel.log');
                
                echo "<h3 class='ok'>✅ Storage structure restored!</h3>";
            }
        } else {
            echo "<p class='error'>❌ Could not remove symlink</p>";
        }
    } else {
        echo "<form method='get'>";
        echo "<input type='hidden' name='fix' value='1'>";
        echo "<button type='submit' class='btn'>🔧 FIX THIS PROBLEM</button>";
        echo "</form>";
    }
}

// Show DB video path
echo "<h2>💾 Check Database Video Path</h2>";
echo "<p>The video_path stored in database should match the actual file location.</p>";
echo "<p>If video was uploaded when symlink was wrong, the path in DB might be incorrect.</p>";

// Test video URL
echo "<h2>🧪 Test Video Access</h2>";

// Find any video
$testVideo = null;
if (file_exists($laravelRoot . '/storage/app/public/announcements/videos')) {
    $vids = glob($laravelRoot . '/storage/app/public/announcements/videos/*.mp4');
    if (count($vids) > 0) {
        $testVideo = $vids[0];
    }
}

if ($testVideo) {
    $filename = basename($testVideo);
    
    // Test different URLs
    $testUrls = [
        '/storage/announcements/videos/' . $filename,
        '/uploads/announcements/videos/' . $filename,
    ];
    
    foreach ($testUrls as $url) {
        echo "<p>Test: <a href='" . $url . "' target='_blank'>" . $url . "</a></p>";
    }
    
    echo "<video src='/storage/announcements/videos/" . $filename . "' controls style='max-width: 300px;'></video>";
} else {
    echo "<p class='warn'>No video files found to test</p>";
}

echo "<hr><p class='error'><strong>DELETE THIS FILE AFTER USE!</strong></p>";
?>
