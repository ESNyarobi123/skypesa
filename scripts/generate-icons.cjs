/**
 * Generate PWA icons and favicon from source logo
 * Run: node scripts/generate-icons.js
 */

const sharp = require('sharp');
const pngToIcoModule = require('png-to-ico');
const pngToIco = pngToIcoModule.default || pngToIcoModule;
const fs = require('fs');
const path = require('path');

// Configuration
const SOURCE_IMAGE = path.join(__dirname, '../public/icons/SKYpesa logo.png');
const OUTPUT_DIR = path.join(__dirname, '../public/icons');
const FAVICON_OUTPUT = path.join(__dirname, '../public/favicon.ico');

// Icon sizes to generate
const ICON_SIZES = [16, 32, 72, 96, 128, 144, 152, 192, 384, 512];

async function generateIcons() {
    console.log('🎨 Generating PWA icons...\n');

    // Check if source image exists
    if (!fs.existsSync(SOURCE_IMAGE)) {
        console.error('❌ Source image not found:', SOURCE_IMAGE);
        process.exit(1);
    }

    // Ensure output directory exists
    if (!fs.existsSync(OUTPUT_DIR)) {
        fs.mkdirSync(OUTPUT_DIR, { recursive: true });
    }

    try {
        // Generate icons for each size
        for (const size of ICON_SIZES) {
            const outputPath = path.join(OUTPUT_DIR, `icon-${size}x${size}.png`);

            await sharp(SOURCE_IMAGE)
                .resize(size, size, {
                    fit: 'contain',
                    background: { r: 17, g: 17, b: 17, alpha: 1 } // #111111 dark background
                })
                .png({ quality: 90, compressionLevel: 9 })
                .toFile(outputPath);

            const stats = fs.statSync(outputPath);
            console.log(`✅ Generated: icon-${size}x${size}.png (${(stats.size / 1024).toFixed(1)}KB)`);
        }

        // Generate maskable icon (with padding for safe zone)
        const maskableOutputPath = path.join(OUTPUT_DIR, 'maskable-icon-512x512.png');
        await sharp(SOURCE_IMAGE)
            .resize(410, 410, { // Smaller to leave padding
                fit: 'contain',
                background: { r: 17, g: 17, b: 17, alpha: 1 }
            })
            .extend({
                top: 51,
                bottom: 51,
                left: 51,
                right: 51,
                background: { r: 17, g: 17, b: 17, alpha: 1 }
            })
            .png({ quality: 90 })
            .toFile(maskableOutputPath);

        const maskableStats = fs.statSync(maskableOutputPath);
        console.log(`✅ Generated: maskable-icon-512x512.png (${(maskableStats.size / 1024).toFixed(1)}KB)`);

        // Generate favicon.ico from 16, 32, and 48px PNGs
        console.log('\n🔷 Generating favicon.ico...');

        // Create temporary 48x48 for favicon
        const temp48Path = path.join(OUTPUT_DIR, 'temp-48x48.png');
        await sharp(SOURCE_IMAGE)
            .resize(48, 48, {
                fit: 'contain',
                background: { r: 17, g: 17, b: 17, alpha: 1 }
            })
            .png()
            .toFile(temp48Path);

        // Generate ICO with multiple sizes
        const icoBuffer = await pngToIco([
            path.join(OUTPUT_DIR, 'icon-16x16.png'),
            path.join(OUTPUT_DIR, 'icon-32x32.png'),
            temp48Path
        ]);

        fs.writeFileSync(FAVICON_OUTPUT, icoBuffer);

        // Clean up temp file
        fs.unlinkSync(temp48Path);

        const faviconStats = fs.statSync(FAVICON_OUTPUT);
        console.log(`✅ Generated: favicon.ico (${(faviconStats.size / 1024).toFixed(1)}KB)`);

        console.log('\n🎉 All icons generated successfully!');
        console.log('\n📁 Files updated:');
        console.log('   - public/favicon.ico');
        console.log('   - public/icons/*.png');

    } catch (error) {
        console.error('❌ Error generating icons:', error);
        process.exit(1);
    }
}

generateIcons();
