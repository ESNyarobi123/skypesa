<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()
            ->latest()
            ->paginate(20);

        // Mark all as read when viewing the list
        auth()->user()->notifications()->where('is_read', false)->update(['is_read' => true]);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        if ($id === 'all') {
            auth()->user()->notifications()->where('is_read', false)->update(['is_read' => true]);
            return back()->with('success', 'Taarifa zote zimewekwa kuwa zimesomwa.');
        }

        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->update(['is_read' => true]);

        return back()->with('success', 'Taarifa imewekwa kuwa imesomwa.');
    }}
