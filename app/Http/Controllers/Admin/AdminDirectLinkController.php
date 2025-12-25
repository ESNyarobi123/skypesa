<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminDirectLinkController extends Controller
{
    /**
     * Display a listing of direct links/ads.
     */
    public function index(Request $request)
    {
        $query = Task::query();
        
        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        
        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('url', 'like', "%{$request->search}%")
                  ->orWhere('provider', 'like', "%{$request->search}%");
            });
        }
        
        $directLinks = $query->withCount('completions')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(20);
        
        // Task types for filter
        $taskTypes = [
            'video_ad' => 'Video Ad',
            'banner_ad' => 'Banner Ad',
            'interstitial' => 'Interstitial',
            'direct_link' => 'Direct Link',
            'native_ad' => 'Native Ad',
            'popunder' => 'Popunder',
            'social_task' => 'Social Task',
            'survey' => 'Survey',
        ];
        
        // Statistics
        $stats = [
            'total_links' => Task::count(),
            'active_links' => Task::where('is_active', true)->count(),
            'total_completions' => Task::sum('completions_count'),
            'earnings_generated' => Task::withSum('completions', 'reward_earned')
                ->get()
                ->sum('completions_sum_reward_earned') ?? 0,
        ];
        
        return view('admin.directlinks.index', compact('directLinks', 'taskTypes', 'stats'));
    }

    /**
     * Show the form for creating a new direct link.
     */
    public function create()
    {
        $taskTypes = [
            'video_ad' => 'Video Ad',
            'banner_ad' => 'Banner Ad',
            'interstitial' => 'Interstitial',
            'direct_link' => 'Direct Link',
            'native_ad' => 'Native Ad', 
            'popunder' => 'Popunder',
            'social_task' => 'Social Task',
            'survey' => 'Survey',
        ];
        
        return view('admin.directlinks.create', compact('taskTypes'));
    }

    /**
     * Store a newly created direct link.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'string', 'max:50'],
            'url' => ['nullable', 'url', 'max:500'],
            'provider' => ['required', 'string', 'max:100'],
            'duration_seconds' => ['required', 'integer', 'min:1', 'max:300'],
            'reward_override' => ['nullable', 'numeric', 'min:0'],
            'daily_limit' => ['nullable', 'integer', 'min:1'],
            'ip_daily_limit' => ['nullable', 'integer', 'min:1'],
            'cooldown_seconds' => ['nullable', 'integer', 'min:0'],
            'total_limit' => ['nullable', 'integer', 'min:1'],
            'category' => ['required', 'in:traffic_task,conversion_task'],
            'require_postback' => ['boolean'],
            'thumbnail' => ['nullable', 'url', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['require_postback'] = $request->boolean('require_postback');
        $validated['completions_count'] = 0;
        $validated['sort_order'] = $validated['sort_order'] ?? Task::max('sort_order') + 1;
        
        // Default anti-fraud values for Direct Links
        $validated['ip_daily_limit'] = $validated['ip_daily_limit'] ?? 5;
        $validated['cooldown_seconds'] = $validated['cooldown_seconds'] ?? 120;
        
        Task::create($validated);
        
        return redirect()
            ->route('admin.directlinks.index')
            ->with('success', 'Direct link/ad created successfully!');
    }

    /**
     * Show the form for editing a direct link.
     */
    public function edit(Task $directlink)
    {
        $taskTypes = [
            'video_ad' => 'Video Ad',
            'banner_ad' => 'Banner Ad',
            'interstitial' => 'Interstitial',
            'direct_link' => 'Direct Link',
            'native_ad' => 'Native Ad',
            'popunder' => 'Popunder',
            'social_task' => 'Social Task',
            'survey' => 'Survey',
        ];
        
        $directlink->loadCount('completions');
        
        return view('admin.directlinks.edit', compact('directlink', 'taskTypes'));
    }

    /**
     * Update the specified direct link.
     */
    public function update(Request $request, Task $directlink)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'string', 'max:50'],
            'url' => ['nullable', 'url', 'max:500'],
            'provider' => ['required', 'string', 'max:100'],
            'duration_seconds' => ['required', 'integer', 'min:1', 'max:300'],
            'reward_override' => ['nullable', 'numeric', 'min:0'],
            'daily_limit' => ['nullable', 'integer', 'min:1'],
            'ip_daily_limit' => ['nullable', 'integer', 'min:1'],
            'cooldown_seconds' => ['nullable', 'integer', 'min:0'],
            'total_limit' => ['nullable', 'integer', 'min:1'],
            'category' => ['required', 'in:traffic_task,conversion_task'],
            'require_postback' => ['boolean'],
            'thumbnail' => ['nullable', 'url', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['require_postback'] = $request->boolean('require_postback');
        
        $directlink->update($validated);
        
        return redirect()
            ->route('admin.directlinks.index')
            ->with('success', 'Direct link/ad updated successfully!');
    }

    /**
     * Remove the specified direct link.
     */
    public function destroy(Task $directlink)
    {
        // Check for completions
        if ($directlink->completions_count > 0) {
            // Soft approach: just deactivate
            $directlink->update(['is_active' => false]);
            return redirect()
                ->route('admin.directlinks.index')
                ->with('success', 'Direct link/ad has been deactivated (has existing completions).');
        }
        
        $directlink->delete();
        
        return redirect()
            ->route('admin.directlinks.index')
            ->with('success', 'Direct link/ad deleted successfully!');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(Task $directlink)
    {
        $directlink->update(['is_active' => !$directlink->is_active]);
        
        $status = $directlink->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Link \"{$directlink->title}\" has been {$status}.");
    }

    /**
     * Bulk toggle status.
     */
    public function bulkToggle(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:tasks,id'],
            'action' => ['required', 'in:activate,deactivate'],
        ]);
        
        $isActive = $request->action === 'activate';
        
        Task::whereIn('id', $request->ids)->update(['is_active' => $isActive]);
        
        $count = count($request->ids);
        $action = $isActive ? 'activated' : 'deactivated';
        
        return back()->with('success', "{$count} links have been {$action}.");
    }

    /**
     * Duplicate a direct link.
     */
    public function duplicate(Task $directlink)
    {
        $newLink = $directlink->replicate();
        $newLink->title = $directlink->title . ' (Copy)';
        $newLink->is_active = false;
        $newLink->completions_count = 0;
        $newLink->sort_order = Task::max('sort_order') + 1;
        $newLink->save();
        
        return redirect()
            ->route('admin.directlinks.edit', $newLink)
            ->with('success', 'Direct link duplicated. You can now edit the copy.');
    }

    /**
     * Analytics for a specific link.
     */
    public function analytics(Task $directlink)
    {
        $directlink->load(['completions' => function($q) {
            $q->with('user')
              ->latest()
              ->take(50);
        }]);
        
        // Daily completions for last 7 days
        $dailyCompletions = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyCompletions[$date] = $directlink->completions()
                ->whereDate('created_at', $date)
                ->count();
        }
        
        // Total stats
        $stats = [
            'total_completions' => $directlink->completions_count,
            'completions_today' => $directlink->completions()->whereDate('created_at', today())->count(),
            'completions_this_week' => $directlink->completions()->where('created_at', '>=', now()->subWeek())->count(),
            'total_rewards_paid' => $directlink->completions()->sum('reward_earned'),
            'unique_users' => $directlink->completions()->distinct('user_id')->count('user_id'),
        ];
        
        return view('admin.directlinks.analytics', compact('directlink', 'dailyCompletions', 'stats'));
    }
}
