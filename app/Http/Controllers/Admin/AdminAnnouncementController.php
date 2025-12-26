<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AdminAnnouncementController extends Controller
{
    /**
     * Display announcements list
     */
    public function index()
    {
        $announcements = Announcement::with('creator')
            ->withCount('reads')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.announcements.index', compact('announcements'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.announcements.create');
    }

    /**
     * Store new announcement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'type' => 'required|in:info,success,warning,urgent',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'nullable',
            'show_as_popup' => 'nullable',
            'max_popup_views' => 'required|integer|min:1|max:10',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? null,
            'is_active' => $request->has('is_active'),
            'show_as_popup' => $request->has('show_as_popup'),
            'max_popup_views' => $validated['max_popup_views'],
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Tangazo limetumwa! "' . $announcement->title . '"');
    }

    /**
     * Show edit form
     */
    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    /**
     * Update announcement
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'type' => 'required|in:info,success,warning,urgent',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'nullable',
            'show_as_popup' => 'nullable',
            'max_popup_views' => 'required|integer|min:1|max:10',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $announcement->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? null,
            'is_active' => $request->has('is_active'),
            'show_as_popup' => $request->has('show_as_popup'),
            'max_popup_views' => $validated['max_popup_views'],
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Tangazo limesasishwa!');
    }

    /**
     * Delete announcement
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Tangazo limefutwa!');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(Announcement $announcement)
    {
        $announcement->update(['is_active' => !$announcement->is_active]);

        $status = $announcement->is_active ? 'limeamilishwa' : 'limezimwa';

        return back()->with('success', "Tangazo {$status}!");
    }

    /**
     * View announcement stats
     */
    public function stats(Announcement $announcement)
    {
        $announcement->load(['reads.user', 'creator']);
        
        $stats = [
            'total_views' => $announcement->reads->sum('view_count'),
            'unique_users' => $announcement->reads->count(),
            'dismissed' => $announcement->reads->where('popup_dismissed', true)->count(),
        ];

        return view('admin.announcements.stats', compact('announcement', 'stats'));
    }
}
