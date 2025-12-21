<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GeneratePwaIcons extends Command
{
    protected $signature = 'pwa:generate-icons {source?}';
    protected $description = 'Generate PWA icons from a source image';

    protected $sizes = [
        16, 32, 72, 96, 128, 144, 152, 192, 384, 512
    ];

    public function handle()
    {
        $sourcePath = $this->argument('source') ?? public_path('icons/icon-512x512.png');
        
        if (!file_exists($sourcePath)) {
            $this->error("Source image not found: {$sourcePath}");
            return 1;
        }

        $outputDir = public_path('icons');
        
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $this->info('Generating PWA icons...');
        $progressBar = $this->output->createProgressBar(count($this->sizes) + 1);
        $progressBar->start();

        try {
            // Create ImageManager instance with GD driver
            $manager = new ImageManager(new Driver());
            
            foreach ($this->sizes as $size) {
                $image = $manager->read($sourcePath);
                $image->cover($size, $size);
                $image->save("{$outputDir}/icon-{$size}x{$size}.png");
                $progressBar->advance();
            }

            // Generate maskable icon (with padding for safe area)
            $image = $manager->read($sourcePath);
            $image->cover(512, 512);
            // Add white background for maskable
            $background = $manager->create(512, 512)->fill('#0a0a0f');
            $background->place($image, 'center');
            $background->save("{$outputDir}/maskable-icon-512x512.png");
            $progressBar->advance();

            $progressBar->finish();
            $this->newLine();
            $this->info('✓ PWA icons generated successfully!');
            $this->info("Icons saved to: {$outputDir}");
            
            return 0;
        } catch (\Exception $e) {
            $progressBar->finish();
            $this->newLine();
            $this->error('Failed to generate icons: ' . $e->getMessage());
            $this->info('');
            $this->info('Make sure you have the Intervention Image library installed:');
            $this->info('composer require intervention/image');
            
            return 1;
        }
    }
}
