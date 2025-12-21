<?php

/**
 * Simple PWA Icon Generator Script
 * Run with: php generate-pwa-icons.php
 */

$sourcePath = __DIR__ . '/public/icons/icon-512x512.png';
$outputDir = __DIR__ . '/public/icons';

$sizes = [16, 32, 72, 96, 128, 144, 152, 192, 384, 512];

if (!file_exists($sourcePath)) {
    echo "Error: Source image not found at {$sourcePath}\n";
    echo "Please place a 512x512 PNG image at public/icons/icon-512x512.png\n";
    exit(1);
}

// Check for GD
if (!extension_loaded('gd')) {
    echo "Note: GD extension not loaded. Creating placeholder references.\n";
    echo "\nTo generate proper icons, you can:\n";
    echo "1. Enable GD in php.ini (uncomment extension=gd)\n";
    echo "2. Use an online PWA icon generator like https://www.pwabuilder.com/imageGenerator\n";
    echo "3. Use a tool like ImageMagick\n\n";
    
    // Create symbolic references or copy the main icon
    foreach ($sizes as $size) {
        $destPath = "{$outputDir}/icon-{$size}x{$size}.png";
        if (!file_exists($destPath)) {
            // Just copy the 512 version as placeholder
            copy($sourcePath, $destPath);
            echo "Created placeholder: icon-{$size}x{$size}.png\n";
        }
    }
    
    // Copy maskable icon
    copy($sourcePath, "{$outputDir}/maskable-icon-512x512.png");
    echo "Created placeholder: maskable-icon-512x512.png\n";
    
    echo "\nPlaceholder icons created. Replace with properly sized versions for production.\n";
    exit(0);
}

echo "Generating PWA icons...\n";

// Load source image
$source = imagecreatefrompng($sourcePath);
if (!$source) {
    echo "Error: Could not load source image\n";
    exit(1);
}

$sourceWidth = imagesx($source);
$sourceHeight = imagesy($source);

foreach ($sizes as $size) {
    $dest = imagecreatetruecolor($size, $size);
    
    // Preserve transparency
    imagealphablending($dest, false);
    imagesavealpha($dest, true);
    $transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
    imagefill($dest, 0, 0, $transparent);
    imagealphablending($dest, true);
    
    // Resize
    imagecopyresampled(
        $dest, $source,
        0, 0, 0, 0,
        $size, $size,
        $sourceWidth, $sourceHeight
    );
    
    $destPath = "{$outputDir}/icon-{$size}x{$size}.png";
    imagepng($dest, $destPath, 9);
    imagedestroy($dest);
    
    echo "Generated: icon-{$size}x{$size}.png\n";
}

// Create maskable icon (with background for safe area)
$maskable = imagecreatetruecolor(512, 512);
$bgColor = imagecolorallocate($maskable, 10, 10, 15);
imagefill($maskable, 0, 0, $bgColor);

// Add padding for safe area (10% on each side)
$padding = 51; // ~10%
$innerSize = 512 - ($padding * 2);

imagecopyresampled(
    $maskable, $source,
    $padding, $padding, 0, 0,
    $innerSize, $innerSize,
    $sourceWidth, $sourceHeight
);

imagepng($maskable, "{$outputDir}/maskable-icon-512x512.png", 9);
imagedestroy($maskable);
echo "Generated: maskable-icon-512x512.png\n";

imagedestroy($source);
echo "\n✓ PWA icons generated successfully!\n";
