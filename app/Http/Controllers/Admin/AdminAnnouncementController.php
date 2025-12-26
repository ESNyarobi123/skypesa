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
        $rules = [
            'title' => 'required|string|max:255',
            'body' => 'nullable|string|max:5000',
            'media_type' => 'required|in:text,video',
            'type' => 'required|in:info,success,warning,urgent',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'nullable',
            'show_as_popup' => 'nullable',
            'max_popup_views' => 'required|integer|min:1|max:10',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
        ];

        // Add video validation if media_type is video
        if ($request->input('media_type') === 'video') {
            $rules['video'] = 'required|file|mimes:mp4|max:15360'; // 15MB max
            $rules['body'] = 'nullable|string|max:5000'; // Body optional for video
        } else {
            $rules['body'] = 'required|string|max:5000';
        }

        $validated = $request->validate($rules);

        // Handle video upload
        $videoPath = null;
        $videoDuration = null;
        if ($request->hasFile('video') && $request->input('media_type') === 'video') {
            $video = $request->file('video');
            $videoPath = $video->store('announcements/videos', 'public');
            
            // Get video duration if possible (use ffprobe or default to client-provided)
            $videoDuration = $request->input('video_duration', 15);
        }

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'body' => $validated['body'] ?? '',
            'media_type' => $validated['media_type'],
            'video_path' => $videoPath,
            'video_duration' => $videoDuration,
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? null,
            'is_active' => $request->has('is_active'),
            'show_as_popup' => $request->has('show_as_popup'),
            'max_popup_views' => $validated['max_popup_views'],
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $typeLabel = $announcement->isVideo() ? '🎬 Video' : '📝 Text';
        return redirect()
            ->route('admin.announcements.index')
            ->with('success', "Tangazo limetumwa! {$typeLabel} \"{$announcement->title}\"");
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
        $rules = [
            'title' => 'required|string|max:255',
            'body' => 'nullable|string|max:5000',
            'media_type' => 'required|in:text,video',
            'type' => 'required|in:info,success,warning,urgent',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'nullable',
            'show_as_popup' => 'nullable',
            'max_popup_views' => 'required|integer|min:1|max:10',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
        ];

        // Add video validation if media_type is video and new video is uploaded
        if ($request->input('media_type') === 'video') {
            if ($request->hasFile('video')) {
                $rules['video'] = 'required|file|mimes:mp4|max:15360'; // 15MB max
            } elseif (!$announcement->video_path) {
                // If switching to video but no existing video and no new upload
                $rules['video'] = 'required|file|mimes:mp4|max:15360';
            }
            $rules['body'] = 'nullable|string|max:5000';
        } else {
            $rules['body'] = 'required|string|max:5000';
        }

        $validated = $request->validate($rules);

        // Handle video upload
        $videoPath = $announcement->video_path;
        $videoDuration = $announcement->video_duration;
        
        if ($request->input('media_type') === 'video') {
            if ($request->hasFile('video')) {
                // Delete old video if exists
                if ($announcement->video_path) {
                    \Storage::disk('public')->delete($announcement->video_path);
                }
                
                $video = $request->file('video');
                $videoPath = $video->store('announcements/videos', 'public');
                $videoDuration = $request->input('video_duration', 15);
            }
        } else {
            // Switching to text - remove video
            if ($announcement->video_path) {
                \Storage::disk('public')->delete($announcement->video_path);
            }
            $videoPath = null;
            $videoDuration = null;
        }

        $announcement->update([
            'title' => $validated['title'],
            'body' => $validated['body'] ?? '',
            'media_type' => $validated['media_type'],
            'video_path' => $videoPath,
            'video_duration' => $videoDuration,
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
