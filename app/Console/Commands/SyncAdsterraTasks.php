<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\AdsterraService;
use Illuminate\Console\Command;

class SyncAdsterraTasks extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'adsterra:sync 
                            {--import : Import new placements as tasks}
                            {--update : Update existing task URLs}
                            {--all : Both import and update}';

    /**
     * The console command description.
     */
    protected $description = 'Sync tasks with Adsterra placements';

    /**
     * Execute the console command.
     */
    public function handle(AdsterraService $adsterra): int
    {
        // Test connection first
        $test = $adsterra->testConnection();
        
        if (!$test['success']) {
            $this->error('Adsterra connection failed: ' . ($test['message'] ?? 'Unknown error'));
            return Command::FAILURE;
        }

        $this->info('Connected to Adsterra!');
        $this->info("Domains: {$test['domains_count']}");

        $importNew = $this->option('import') || $this->option('all');
        $updateExisting = $this->option('update') || $this->option('all');

        if (!$importNew && !$updateExisting) {
            $this->info('Specify --import, --update, or --all');
            return Command::SUCCESS;
        }

        $placements = $adsterra->getTaskablePlacements();
        $this->info('Taskable placements found: ' . count($placements));

        $imported = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($placements as $placement) {
            $existing = Task::where('provider', 'adsterra')
                ->whereJsonContains('requirements->adsterra_placement_id', $placement['id'])
                ->first();

            if ($existing) {
                if ($updateExisting && !empty($placement['direct_url']) && $existing->url !== $placement['direct_url']) {
                    $existing->update(['url' => $placement['direct_url']]);
                    $updated++;
                    $this->line("Updated: {$existing->title}");
                } else {
                    $skipped++;
                }
            } elseif ($importNew) {
                $taskData = $adsterra->placementToTaskData($placement);
                Task::create($taskData);
                $imported++;
                $this->line("Imported: {$taskData['title']}");
            }
        }

        $this->newLine();
        $this->info("Results:");
        $this->info("  Imported: {$imported}");
        $this->info("  Updated: {$updated}");
        $this->info("  Skipped: {$skipped}");

        return Command::SUCCESS;
    }
}
