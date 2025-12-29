<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Get active announcements for the user
     * GET /api/v1/announcements
     * 
     * Returns announcements that should show as popups + all active ones
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get all active announcements
        $announcements = Announcement::getActiveAnnouncements();
        
        // Separate popup announcements from regular ones
        $popupAnnouncements = [];
        $allAnnouncements = [];
        
        foreach ($announcements as $announcement) {
            $announcementData = $this->formatAnnouncement($announcement, $user);
            
            // Check if should show as popup
            if ($announcement->shouldShowPopupFor($user)) {
                $popupAnnouncements[] = $announcementData;
            }
            
            $allAnnouncements[] = $announcementData;
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'popup' => $popupAnnouncements,     // Show these as dialog/popup
                'all' => $allAnnouncements,         // All announcements for list view
                'popup_count' => count($popupAnnouncements),
                'total_count' => count($allAnnouncements),
            ],
        ]);
    }

    /**
     * Get single announcement details
     * GET /api/v1/announcements/{id}
     */
    public function show(Request $request, Announcement $announcement)
    {
        $user = $request->user();

        if (!$announcement->isCurrentlyActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Tangazo hili halipo.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatAnnouncement($announcement, $user),
        ]);
    }

    /**
     * Record that user viewed/dismissed an announcement popup
     * POST /api/v1/announcements/{id}/dismiss
     */
    public function dismiss(Request $request, Announcement $announcement)
    {
        $user = $request->user();
        
        if (!$announcement->isCurrentlyActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Tangazo hili halipo.',
            ], 404);
        }
        
        // Record the view
        $read = $announcement->recordView($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Tangazo limeonekana.',
            'data' => [
                'announcement_id' => $announcement->id,
                'view_count' => $read->view_count,
                'popup_dismissed' => $read->popup_dismissed,
                'will_show_again' => !$read->popup_dismissed && ($read->view_count < $announcement->max_popup_views),
                'remaining_views' => max(0, $announcement->max_popup_views - $read->view_count),
            ],
        ]);
    }

    /**
     * Get announcement history (all announcements with read status)
     * GET /api/v1/announcements/history
     */
    public function history(Request $request)
    {
        $user = $request->user();
        
        $announcements = Announcement::getActiveAnnouncements()
            ->map(function ($announcement) use ($user) {
                $read = $announcement->reads()->where('user_id', $user->id)->first();
                
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'body' => $announcement->body,
                    'type' => $announcement->type,
                    'type_label' => $this->getTypeLabel($announcement->type),
                    'icon' => $announcement->getTypeIcon(),
                    'color' => $announcement->getTypeBadgeColor(),
                    'media_type' => $announcement->media_type ?? 'text',
                    'is_video' => $announcement->isVideo(),
                    'video_url' => $announcement->video_url,
                    'video_duration' => $announcement->video_duration,
                    'is_read' => $read !== null,
                    'view_count' => $read?->view_count ?? 0,
                    'first_seen_at' => $read?->first_seen_at?->toIso8601String(),
                    'last_seen_at' => $read?->last_seen_at?->toIso8601String(),
                    'created_at' => $announcement->created_at->toIso8601String(),
                    'created_at_human' => $announcement->created_at->diffForHumans(),
                ];
            });
        
        // Count unread
        $unreadCount = $announcements->filter(fn($a) => !$a['is_read'])->count();
        
        return response()->json([
            'success' => true,
            'data' => $announcements,
            'meta' => [
                'total' => $announcements->count(),
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    /**
     * Mark all announcements as read
     * POST /api/v1/announcements/read-all
     */
    public function readAll(Request $request)
    {
        $user = $request->user();
        
        $announcements = Announcement::getActiveAnnouncements();
        $markedCount = 0;
        
        foreach ($announcements as $announcement) {
            $read = $announcement->reads()->where('user_id', $user->id)->first();
            
            if (!$read) {
                $announcement->recordView($user);
                $markedCount++;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "Matangazo {$markedCount} yamesomwa.",
            'data' => [
                'marked_count' => $markedCount,
            ],
        ]);
    }

    /**
     * Format announcement data for response
     */
    private function formatAnnouncement(Announcement $announcement, $user): array
    {
        $read = $announcement->reads()->where('user_id', $user->id)->first();
        
        return [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'body' => $announcement->body,
            'body_preview' => \Str::limit(strip_tags($announcement->body), 100),
            'type' => $announcement->type,
            'type_label' => $this->getTypeLabel($announcement->type),
            'icon' => $announcement->getTypeIcon(),
            'color' => $announcement->getTypeBadgeColor(),
            'media_type' => $announcement->media_type ?? 'text',
            'is_video' => $announcement->isVideo(),
            'video_url' => $announcement->video_url,
            'video_duration' => $announcement->video_duration,
            'video_duration_formatted' => $announcement->video_duration 
                ? gmdate('i:s', $announcement->video_duration) 
                : null,
            'show_as_popup' => $announcement->show_as_popup,
            'max_popup_views' => $announcement->max_popup_views,
            'is_read' => $read !== null,
            'view_count' => $read?->view_count ?? 0,
            'starts_at' => $announcement->starts_at?->toIso8601String(),
            'expires_at' => $announcement->expires_at?->toIso8601String(),
            'created_at' => $announcement->created_at->toIso8601String(),
            'created_at_human' => $announcement->created_at->diffForHumans(),
        ];
    }

    /**
     * Get human-readable type label
     */
    private function getTypeLabel(string $type): string
    {
        return match($type) {
            'info' => 'Taarifa',
            'success' => 'Mafanikio',
            'warning' => 'Onyo',
            'urgent' => 'Dharura',
            default => 'Taarifa',
        };
    }
}
