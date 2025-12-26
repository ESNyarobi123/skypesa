<?php
/**
 * Create storage symbolic link for shared hosting
 * Access this file ONCE via browser: https://yoursite.com/link.php
 * Then DELETE this file for security
 */

// Paths
$target = $_SERVER['DOCUMENT_ROOT'] . '/../storage/app/public';
$link = $_SERVER['DOCUMENT_ROOT'] . '/storage';

// Alternative paths for different hosting setups
if (!file_exists($target)) {
    $target = dirname($_SERVER['DOCUMENT_ROOT']) . '/storage/app/public';
}

// Check if link already exists
if (file_exists($link)) {
    if (is_link($link)) {
        echo "✅ Storage link already exists!<br>";
        echo "Link: " . $link . "<br>";
        echo "Target: " . readlink($link) . "<br>";
    } else {
        echo "⚠️ '/storage' directory exists but is not a symbolic link.<br>";
        echo "You may need to remove it manually and run this script again.<br>";
    }
    exit;
}

// Check if target exists
if (!file_exists($target)) {
    echo "❌ Target directory does not exist!<br>";
    echo "Looking for: " . $target . "<br>";
    echo "<br>Please create the following directories on your server:<br>";
    echo "- storage/app/public/announcements/videos/<br>";
    exit;
}

// Try to create the symbolic link
try {
    if (symlink($target, $link)) {
        echo "✅ SUCCESS! Storage link created!<br>";
        echo "Link: " . $link . "<br>";
        echo "Target: " . $target . "<br>";
        echo "<br><strong>⚠️ IMPORTANT: Delete this file (link.php) now for security!</strong>";
    } else {
        echo "❌ Failed to create symbolic link.<br>";
        echo "Your hosting may not support symlinks.<br>";
        echo "<br><strong>Alternative Solution:</strong><br>";
        echo "1. Ask your hosting provider to enable symlinks<br>";
        echo "2. Or manually copy files from storage/app/public to public/storage<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<br>Try these alternatives:<br>";
    echo "1. Use cPanel File Manager to create symbolic link<br>";
    echo "2. Contact hosting support<br>";
}
?>
