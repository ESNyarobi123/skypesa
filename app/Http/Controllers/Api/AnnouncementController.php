<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Get active announcements for the user
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
            $announcementData = [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'body' => $announcement->body,
                'type' => $announcement->type,
                'icon' => $announcement->getTypeIcon(),
                'color' => $announcement->getTypeBadgeColor(),
                'created_at' => $announcement->created_at->toISOString(),
            ];
            
            // Check if should show as popup
            if ($announcement->shouldShowPopupFor($user)) {
                $popupAnnouncements[] = $announcementData;
            }
            
            $allAnnouncements[] = $announcementData;
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'popup' => $popupAnnouncements, // Show these as dialog
                'all' => $allAnnouncements,     // All announcements for history
            ],
        ]);
    }

    /**
     * Record that user viewed/dismissed an announcement popup
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
                'view_count' => $read->view_count,
                'popup_dismissed' => $read->popup_dismissed,
                'will_show_again' => !$read->popup_dismissed,
            ],
        ]);
    }

    /**
     * Get announcement history (all announcements user has seen)
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
                    'icon' => $announcement->getTypeIcon(),
                    'color' => $announcement->getTypeBadgeColor(),
                    'created_at' => $announcement->created_at->toISOString(),
                    'is_read' => $read !== null,
                    'view_count' => $read?->view_count ?? 0,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $announcements,
        ]);
    }
}
