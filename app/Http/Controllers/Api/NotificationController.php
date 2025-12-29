<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all notifications
     * GET /api/v1/notifications
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get filter params
        $type = $request->query('type');
        $unreadOnly = $request->boolean('unread_only', false);

        $query = $user->notifications()->latest();
        
        if ($type) {
            $query->where('type', $type);
        }
        
        if ($unreadOnly) {
            $query->where('is_read', false);
        }

        $notifications = $query->paginate(20);

        $data = $notifications->getCollection()->map(function ($notification) {
            return $this->formatNotification($notification);
        });

        // Count unread
        $unreadCount = $user->notifications()->unread()->count();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    /**
     * Get single notification
     * GET /api/v1/notifications/{id}
     */
    public function show(Request $request, $notificationId)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Arifa haijapatikana.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatNotification($notification),
        ]);
    }

    /**
     * Get unread count
     * GET /api/v1/notifications/unread-count
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $count = $user->notifications()->unread()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count,
                'has_unread' => $count > 0,
            ],
        ]);
    }

    /**
     * Mark notification as read
     * PUT /api/v1/notifications/{id}/read
     */
    public function markAsRead(Request $request, $notificationId)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Arifa haijapatikana.',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Arifa imesomwa.',
            'data' => [
                'id' => $notification->id,
                'is_read' => true,
            ],
        ]);
    }

    /**
     * Mark all as read
     * PUT /api/v1/notifications/read-all
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $count = $user->notifications()->unread()->count();
        
        $user->notifications()->unread()->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => "Arifa {$count} zimesomwa.",
            'data' => [
                'marked_count' => $count,
            ],
        ]);
    }

    /**
     * Delete notification
     * DELETE /api/v1/notifications/{id}
     */
    public function destroy(Request $request, $notificationId)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Arifa haijapatikana.',
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Arifa imefutwa.',
        ]);
    }

    /**
     * Delete all notifications
     * DELETE /api/v1/notifications/clear-all
     */
    public function clearAll(Request $request)
    {
        $user = $request->user();
        $count = $user->notifications()->count();
        
        $user->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => "Arifa {$count} zimefutwa.",
            'data' => [
                'deleted_count' => $count,
            ],
        ]);
    }

    /**
     * Get notifications by type
     * GET /api/v1/notifications/types
     */
    public function types(Request $request)
    {
        $user = $request->user();
        
        $types = $user->notifications()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                $notification = new Notification(['type' => $item->type]);
                return [
                    'type' => $item->type,
                    'label' => $notification->getTypeLabel(),
                    'icon' => $notification->getIconName(),
                    'color' => $notification->getColor(),
                    'count' => $item->count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * Format notification for response
     */
    private function formatNotification(Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'type_label' => $notification->getTypeLabel(),
            'icon' => $notification->getIconName(),
            'color' => $notification->getColor(),
            'title' => $notification->title,
            'message' => $notification->message,
            'data' => $notification->data,
            'is_read' => $notification->is_read,
            'created_at' => $notification->created_at->toIso8601String(),
            'created_at_human' => $notification->created_at->diffForHumans(),
            'created_at_formatted' => $notification->created_at->format('d M Y, H:i'),
        ];
    }
}
