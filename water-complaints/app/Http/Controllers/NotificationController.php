<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function getNotificationCount()
    {
        $user = auth()->user();
        $pendingConfirmations = Notification::where('user_id', $user->id)
            ->where('type', 'status_confirmation_required')
            ->where('action_required', true)
            ->count();

        $unread = Notification::where('user_id', $user->id)
            ->where('read_at', null)
            ->count();

        return response()->json([
            'pending_confirmations' => $pendingConfirmations,
            'unread' => $unread,
        ]);
    }

    public function index()
    {
        $user = auth()->user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update(['read_at' => now()]);
        return back()->with('success', 'Notification marked as read.');
    }
}