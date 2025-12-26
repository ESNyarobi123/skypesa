<?php
/**
 * Check storage configuration for video announcements
 * Access via: https://yoursite.com/storage-check.php
 * DELETE this file after checking!
 */

echo "<h1>📹 Video Storage Check</h1>";
echo "<style>body { font-family: Arial; padding: 20px; background: #1a1a1a; color: #fff; } 
.ok { color: #10b981; } .error { color: #ef4444; } .warn { color: #f59e0b; }
pre { background: #333; padding: 10px; border-radius: 5px; overflow-x: auto; }</style>";

// Paths
$publicPath = $_SERVER['DOCUMENT_ROOT'];
$storagePath = dirname($publicPath) . '/storage/app/public';
$storageLink = $publicPath . '/storage';
$videoDir = $storagePath . '/announcements/videos';

echo "<h2>1️⃣ Path Configuration</h2>";
echo "<pre>";
echo "Public Path: " . $publicPath . "\n";
echo "Storage Path: " . $storagePath . "\n";
echo "Storage Link: " . $storageLink . "\n";
echo "Video Directory: " . $videoDir . "\n";
echo "</pre>";

echo "<h2>2️⃣ Directory Status</h2>";

// Check storage/app/public
echo "<p>Storage App Public: ";
if (file_exists($storagePath)) {
    echo "<span class='ok'>✅ EXISTS</span>";
} else {
    echo "<span class='error'>❌ NOT FOUND</span>";
}
echo "</p>";

// Check public/storage (symlink)
echo "<p>Public Storage Link: ";
if (file_exists($storageLink)) {
    if (is_link($storageLink)) {
        echo "<span class='ok'>✅ SYMLINK EXISTS</span> → " . readlink($storageLink);
    } else {
        echo "<span class='warn'>⚠️ EXISTS (but not a symlink)</span>";
    }
} else {
    echo "<span class='error'>❌ NOT FOUND - Run link.php first!</span>";
}
echo "</p>";

// Check video directory
echo "<p>Video Directory: ";
if (file_exists($videoDir)) {
    echo "<span class='ok'>✅ EXISTS</span>";
    
    // List videos
    $videos = glob($videoDir . '/*.mp4');
    if (count($videos) > 0) {
        echo "<br>📹 Videos found: " . count($videos);
        echo "<ul>";
        foreach ($videos as $video) {
            $filename = basename($video);
            $size = round(filesize($video) / 1024 / 1024, 2);
            echo "<li>" . $filename . " (" . $size . " MB)</li>";
        }
        echo "</ul>";
    } else {
        echo "<br><span class='warn'>⚠️ No videos uploaded yet</span>";
    }
} else {
    echo "<span class='error'>❌ NOT FOUND</span>";
    echo "<br>Create it: <code>mkdir -p storage/app/public/announcements/videos</code>";
}
echo "</p>";

echo "<h2>3️⃣ Permissions</h2>";

// Check write permissions
echo "<p>Storage writable: ";
if (is_writable($storagePath)) {
    echo "<span class='ok'>✅ YES</span>";
} else {
    echo "<span class='error'>❌ NO - chmod 755 or 775 needed</span>";
}
echo "</p>";

// Check video dir permissions
if (file_exists($videoDir)) {
    echo "<p>Video dir writable: ";
    if (is_writable($videoDir)) {
        echo "<span class='ok'>✅ YES</span>";
    } else {
        echo "<span class='error'>❌ NO - chmod 755 or 775 needed</span>";
    }
    echo "</p>";
}

echo "<h2>4️⃣ Test Video URL</h2>";

// Check if there's any video to test
$testVideos = glob($videoDir . '/*.mp4');
if (count($testVideos) > 0) {
    $firstVideo = basename($testVideos[0]);
    $videoUrl = '/storage/announcements/videos/' . $firstVideo;
    echo "<p>Test URL: <a href='" . $videoUrl . "' target='_blank'>" . $videoUrl . "</a></p>";
    echo "<video src='" . $videoUrl . "' controls style='max-width: 400px; border-radius: 8px;'></video>";
} else {
    echo "<p class='warn'>⚠️ No videos to test. Upload a video first through admin panel.</p>";
}

echo "<h2>5️⃣ .env Configuration</h2>";
$envFile = dirname($publicPath) . '/.env';
if (file_exists($envFile)) {
    $env = file_get_contents($envFile);
    
    // Get APP_URL
    preg_match('/APP_URL=(.*)/', $env, $matches);
    $appUrl = isset($matches[1]) ? trim($matches[1]) : 'Not set';
    
    echo "<pre>APP_URL=" . $appUrl . "</pre>";
    
    if (strpos($appUrl, 'http://127.0.0.1') !== false || strpos($appUrl, 'http://localhost') !== false) {
        echo "<p class='error'>❌ APP_URL is set to localhost! Change it to your domain.</p>";
    } else {
        echo "<p class='ok'>✅ APP_URL looks correct</p>";
    }
} else {
    echo "<p class='error'>❌ .env file not found</p>";
}

echo "<hr><p><strong>⚠️ DELETE THIS FILE (storage-check.php) AFTER USE!</strong></p>";
?>
