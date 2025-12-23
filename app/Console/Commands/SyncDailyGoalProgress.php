<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SyncDailyGoalProgress extends Command
{
    protected $signature = 'daily-goal:sync';
    protected $description = 'Sync daily goal progress for all users based on completed tasks today';

    public function handle()
    {
        $this->info('Syncing daily goal progress for all users...');
        
        $users = User::all();
        $synced = 0;
        
        foreach ($users as $user) {
            $tasksCompletedToday = $user->taskCompletions()
                ->where('status', 'completed')
                ->whereDate('created_at', today())
                ->count();
            
            if ($tasksCompletedToday > 0) {
                $user->update([
                    'last_daily_goal_date' => today()->toDateString(),
                    'daily_goal_progress' => $tasksCompletedToday,
                ]);
                $synced++;
                $this->line("  User {$user->id} ({$user->name}): {$tasksCompletedToday} tasks");
            }
        }
        
        $this->info("Synced {$synced} users.");
        
        return Command::SUCCESS;
    }
}
