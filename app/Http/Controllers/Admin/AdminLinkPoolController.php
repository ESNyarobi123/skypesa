<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LinkPool;
use App\Models\PoolLink;
use Illuminate\Http\Request;

/**
 * Admin Link Pool Controller
 * 
 * Manages link pools (SkyBoost™, SkyLinks™) and their links.
 * Supports random link rotation for ad distribution.
 */
class AdminLinkPoolController extends Controller
{
    /**
     * Display all link pools
     */
    public function index()
    {
        $pools = LinkPool::withCount(['links', 'activeLinks'])
            ->orderBy('sort_order')
            ->get();

        $stats = [
            'total_pools' => LinkPool::count(),
            'active_pools' => LinkPool::where('is_active', true)->count(),
            'total_links' => PoolLink::count(),
            'total_clicks_today' => PoolLink::sum('clicks_today'),
        ];

        return view('admin.linkpools.index', compact('pools', 'stats'));
    }

    /**
     * Show form to create a new pool
     */
    public function create()
    {
        return view('admin.linkpools.create');
    }

    /**
     * Store a new pool
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:50', 'unique:link_pools,slug', 'regex:/^[a-z0-9_]+$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
            'reward_amount' => ['required', 'numeric', 'min:0', 'max:1000'],
            'duration_seconds' => ['required', 'integer', 'min:5', 'max:300'],
            'daily_user_limit' => ['nullable', 'integer', 'min:1'],
            'daily_global_limit' => ['nullable', 'integer', 'min:1'],
            'cooldown_seconds' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = LinkPool::max('sort_order') + 1;

        LinkPool::create($validated);

        return redirect()
            ->route('admin.linkpools.index')
            ->with('success', 'Link Pool "' . $validated['name'] . '" created successfully!');
    }

    /**
     * Show pool details with all links
     */
    public function show(LinkPool $linkpool)
    {
        $linkpool->load('links');
        
        $stats = [
            'total_links' => $linkpool->links->count(),
            'active_links' => $linkpool->links->where('is_active', true)->count(),
            'clicks_today' => $linkpool->links->sum('clicks_today'),
            'total_clicks' => $linkpool->links->sum('total_clicks'),
        ];

        return view('admin.linkpools.show', compact('linkpool', 'stats'));
    }

    /**
     * Show form to edit pool
     */
    public function edit(LinkPool $linkpool)
    {
        return view('admin.linkpools.edit', compact('linkpool'));
    }

    /**
     * Update pool
     */
    public function update(Request $request, LinkPool $linkpool)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:50', 'unique:link_pools,slug,' . $linkpool->id, 'regex:/^[a-z0-9_]+$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
            'reward_amount' => ['required', 'numeric', 'min:0', 'max:1000'],
            'duration_seconds' => ['required', 'integer', 'min:5', 'max:300'],
            'daily_user_limit' => ['nullable', 'integer', 'min:1'],
            'daily_global_limit' => ['nullable', 'integer', 'min:1'],
            'cooldown_seconds' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $linkpool->update($validated);

        return redirect()
            ->route('admin.linkpools.index')
            ->with('success', 'Link Pool updated successfully!');
    }

    /**
     * Delete pool
     */
    public function destroy(LinkPool $linkpool)
    {
        $name = $linkpool->name;
        $linkpool->delete();

        return redirect()
            ->route('admin.linkpools.index')
            ->with('success', 'Link Pool "' . $name . '" deleted successfully!');
    }

    /**
     * Toggle pool active status
     */
    public function toggleStatus(LinkPool $linkpool)
    {
        $linkpool->update(['is_active' => !$linkpool->is_active]);
        
        $status = $linkpool->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Pool \"{$linkpool->name}\" has been {$status}.");
    }

    // ========================================
    // POOL LINKS MANAGEMENT
    // ========================================

    /**
     * Show form to add link to pool
     */
    public function createLink(LinkPool $linkpool)
    {
        return view('admin.linkpools.links.create', compact('linkpool'));
    }

    /**
     * Store new link in pool
     */
    public function storeLink(Request $request, LinkPool $linkpool)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url', 'max:500'],
            'provider' => ['required', 'string', 'max:50'],
            'weight' => ['nullable', 'integer', 'min:1', 'max:100'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['weight'] = $validated['weight'] ?? 1;
        $validated['link_pool_id'] = $linkpool->id;

        PoolLink::create($validated);

        return redirect()
            ->route('admin.linkpools.show', $linkpool)
            ->with('success', 'Link added to pool successfully!');
    }

    /**
     * Show form to edit link
     */
    public function editLink(LinkPool $linkpool, PoolLink $link)
    {
        return view('admin.linkpools.links.edit', compact('linkpool', 'link'));
    }

    /**
     * Update link
     */
    public function updateLink(Request $request, LinkPool $linkpool, PoolLink $link)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url', 'max:500'],
            'provider' => ['required', 'string', 'max:50'],
            'weight' => ['nullable', 'integer', 'min:1', 'max:100'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $link->update($validated);

        return redirect()
            ->route('admin.linkpools.show', $linkpool)
            ->with('success', 'Link updated successfully!');
    }

    /**
     * Delete link
     */
    public function destroyLink(LinkPool $linkpool, PoolLink $link)
    {
        $link->delete();

        return redirect()
            ->route('admin.linkpools.show', $linkpool)
            ->with('success', 'Link removed from pool!');
    }

    /**
     * Toggle link active status
     */
    public function toggleLinkStatus(LinkPool $linkpool, PoolLink $link)
    {
        $link->update(['is_active' => !$link->is_active]);
        
        $status = $link->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Link \"{$link->name}\" has been {$status}.");
    }

    /**
     * Reset clicks for a link
     */
    public function resetLinkClicks(LinkPool $linkpool, PoolLink $link)
    {
        $link->update([
            'total_clicks' => 0,
            'clicks_today' => 0,
        ]);

        return back()->with('success', 'Click stats reset for "' . $link->name . '"');
    }

    /**
     * Bulk import links (for quick setup)
     */
    public function bulkImportLinks(Request $request, LinkPool $linkpool)
    {
        $validated = $request->validate([
            'links' => ['required', 'string'],
            'provider' => ['required', 'string', 'max:50'],
        ]);

        $lines = array_filter(explode("\n", $validated['links']));
        $imported = 0;

        foreach ($lines as $index => $url) {
            $url = trim($url);
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                PoolLink::create([
                    'link_pool_id' => $linkpool->id,
                    'name' => $validated['provider'] . ' Link ' . ($index + 1),
                    'url' => $url,
                    'provider' => $validated['provider'],
                    'is_active' => true,
                    'weight' => 1,
                ]);
                $imported++;
            }
        }

        return back()->with('success', "Successfully imported {$imported} links!");
    }
}
