<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\LinkPool;
use Illuminate\Http\Request;

class AdminTaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('linkPool')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('admin.tasks.index', compact('tasks'));
    }

    public function create()
    {
        $linkPools = LinkPool::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('admin.tasks.create', compact('linkPools'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:view_ad,share_link,survey,other',
            'url' => 'nullable|url',
            'link_pool_id' => 'nullable|exists:link_pools,id',
            'provider' => 'required|in:monetag,adsterra,custom',
            'duration_seconds' => 'required|integer|min:5|max:300',
            'reward_override' => 'nullable|numeric|min:0',
            'daily_limit' => 'nullable|integer|min:1',
            'total_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);
        
        // Require either URL or Link Pool
        if (empty($validated['url']) && empty($validated['link_pool_id'])) {
            return back()->withErrors(['url' => 'Lazima uweke URL au uchague Link Pool.'])->withInput();
        }
        
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        
        // If using link pool, set URL to empty string (it will be overridden at runtime)
        if (!empty($validated['link_pool_id'])) {
            $validated['url'] = $validated['url'] ?: '';
        }
        
        Task::create($validated);
        
        return redirect()->route('admin.tasks.index')
            ->with('success', 'Kazi imeongezwa!');
    }

    public function edit(Task $task)
    {
        $linkPools = LinkPool::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('admin.tasks.edit', compact('task', 'linkPools'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:view_ad,share_link,survey,other',
            'url' => 'nullable|url',
            'link_pool_id' => 'nullable|exists:link_pools,id',
            'provider' => 'required|in:monetag,adsterra,custom',
            'duration_seconds' => 'required|integer|min:5|max:300',
            'reward_override' => 'nullable|numeric|min:0',
            'daily_limit' => 'nullable|integer|min:1',
            'total_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);
        
        // Require either URL or Link Pool
        if (empty($validated['url']) && empty($validated['link_pool_id'])) {
            return back()->withErrors(['url' => 'Lazima uweke URL au uchague Link Pool.'])->withInput();
        }
        
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        
        // If using link pool, clear the static URL (or keep as fallback)
        if (!empty($validated['link_pool_id'])) {
            $validated['url'] = $validated['url'] ?: '';
        } else {
            $validated['link_pool_id'] = null;
        }
        
        $task->update($validated);
        
        return redirect()->route('admin.tasks.index')
            ->with('success', 'Kazi imebadilishwa!');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        
        return redirect()->route('admin.tasks.index')
            ->with('success', 'Kazi imefutwa!');
    }
}
