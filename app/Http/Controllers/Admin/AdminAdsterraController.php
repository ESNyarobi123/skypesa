<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\AdsterraService;
use Illuminate\Http\Request;

class AdminAdsterraController extends Controller
{
    protected AdsterraService $adsterra;

    public function __construct(AdsterraService $adsterra)
    {
        $this->adsterra = $adsterra;
    }

    /**
     * Show Adsterra integration page
     */
    public function index()
    {
        $connectionTest = $this->adsterra->testConnection();
        $domains = [];
        $placements = [];
        $taskablePlacements = [];
        
        if ($connectionTest['success']) {
            $domains = $this->adsterra->getDomains();
            $placements = $this->adsterra->getPlacements();
            $taskablePlacements = $this->adsterra->getTaskablePlacements();
        }
        
        // Get existing Adsterra tasks
        $existingTasks = Task::where('provider', 'adsterra')->get();
        
        return view('admin.adsterra.index', compact(
            'connectionTest',
            'domains',
            'placements',
            'taskablePlacements',
            'existingTasks'
        ));
    }

    /**
     * Refresh/clear cache and refetch data
     */
    public function refresh()
    {
        $this->adsterra->clearCache();
        
        return redirect()->route('admin.adsterra.index')
            ->with('success', 'Data imesasishwa kutoka Adsterra!');
    }

    /**
     * Import a single placement as a task
     */
    public function importPlacement(Request $request)
    {
        $request->validate([
            'placement_id' => 'required|integer',
            'direct_url' => 'required|url',
            'title' => 'required|string|max:255',
        ]);

        // Check if already imported
        $existing = Task::where('provider', 'adsterra')
            ->whereJsonContains('requirements->adsterra_placement_id', $request->placement_id)
            ->first();

        if ($existing) {
            return back()->with('error', 'Placement hii imeshaingizwa kama task.');
        }

        Task::create([
            'title' => $request->title,
            'description' => $request->input('description', 'Tazama tangazo hili kwa sekunde 30 na upate malipo.'),
            'type' => 'view_ad',
            'url' => $request->direct_url,
            'provider' => 'adsterra',
            'duration_seconds' => $request->input('duration_seconds', 30),
            'daily_limit' => $request->input('daily_limit', 3),
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 10,
            'requirements' => [
                'adsterra_placement_id' => $request->placement_id,
            ],
        ]);

        return back()->with('success', 'Task imeongezwa kutoka Adsterra!');
    }

    /**
     * Import all taskable placements
     */
    public function importAll()
    {
        $placements = $this->adsterra->getTaskablePlacements();
        $imported = 0;
        $skipped = 0;

        foreach ($placements as $placement) {
            // Skip if already imported
            $existing = Task::where('provider', 'adsterra')
                ->whereJsonContains('requirements->adsterra_placement_id', $placement['id'])
                ->first();

            if ($existing) {
                $skipped++;
                continue;
            }

            $taskData = $this->adsterra->placementToTaskData($placement);
            Task::create($taskData);
            $imported++;
        }

        if ($imported > 0) {
            return back()->with('success', "Tasks {$imported} zimeingizwa! ({$skipped} zimepitishwa)");
        }

        return back()->with('info', 'Hakuna tasks mpya za kuingiza.');
    }

    /**
     * Sync existing tasks with Adsterra (update URLs if changed)
     */
    public function sync()
    {
        $placements = $this->adsterra->getPlacements();
        $placementsById = collect($placements)->keyBy('id');
        
        $tasks = Task::where('provider', 'adsterra')
            ->whereNotNull('requirements')
            ->get();
        
        $updated = 0;
        
        foreach ($tasks as $task) {
            $requirements = $task->requirements;
            $placementId = $requirements['adsterra_placement_id'] ?? null;
            
            if ($placementId && isset($placementsById[$placementId])) {
                $placement = $placementsById[$placementId];
                
                if (!empty($placement['direct_url']) && $task->url !== $placement['direct_url']) {
                    $task->update(['url' => $placement['direct_url']]);
                    $updated++;
                }
            }
        }

        return back()->with('success', "Tasks {$updated} zimesasishwa!");
    }
}
