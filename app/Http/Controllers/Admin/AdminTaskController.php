<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class AdminTaskController extends Controller
{
    public function index()
    {
        $tasks = Task::orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('admin.tasks.index', compact('tasks'));
    }

    public function create()
    {
        return view('admin.tasks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:view_ad,share_link,survey,other',
            'url' => 'required|url',
            'provider' => 'required|in:monetag,adsterra,custom',
            'duration_seconds' => 'required|integer|min:5|max:300',
            'reward_override' => 'nullable|numeric|min:0',
            'daily_limit' => 'nullable|integer|min:1',
            'total_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);
        
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        
        Task::create($validated);
        
        return redirect()->route('admin.tasks.index')
            ->with('success', 'Kazi imeongezwa!');
    }

    public function edit(Task $task)
    {
        return view('admin.tasks.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:view_ad,share_link,survey,other',
            'url' => 'required|url',
            'provider' => 'required|in:monetag,adsterra,custom',
            'duration_seconds' => 'required|integer|min:5|max:300',
            'reward_override' => 'nullable|numeric|min:0',
            'daily_limit' => 'nullable|integer|min:1',
            'total_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);
        
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        
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
