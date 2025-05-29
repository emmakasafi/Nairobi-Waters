<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\WaterSentiment;
use App\Models\StatusUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {
        // Fetch paginated notifications for the authenticated user
        $notifications = Notification::where('user_id', Auth::id())
            ->where('type', 'status_confirmation_required')
            ->latest()
            ->paginate(10); // 10 notifications per page

        // Map notifications to include WaterSentiment and StatusUpdate data
        $notifications->getCollection()->transform(function ($notification) {
            // Parse JSON data
            $notification->complaint_data = json_decode($notification->data, true);

            // Fetch WaterSentiment manually
            $notification->waterSentiment = WaterSentiment::with(['statusUpdates' => function ($query) {
                $query->where('status', 'pending_confirmation')->latest()->limit(1);
            }])
                ->where('id', $notification->complaint_data['water_sentiment_id'] ?? 0)
                ->first();

            return $notification;
        });

        return view('customer.notifications.index', compact('notifications'));
    }

    public function respond(Request $request, Notification $notification)
    {
        $request->validate([
            'response' => 'required|in:confirmed,rejected', // Match view's values
            'rejection_reason' => 'required_if:response,rejected|string|max:1000|nullable',
        ]);

        if ($notification->user_id !== Auth::id()) {
            Log::warning('Unauthorized notification response attempt', [
                'user_id' => Auth::id(),
                'notification_id' => $notification->id,
            ]);
            abort(403, 'Unauthorized action.');
        }

        try {
            // Decode JSON data
            $data = json_decode($notification->data, true);

            // Ensure data is an array
            if (!is_array($data)) {
                Log::error('Invalid notification data format', [
                    'notification_id' => $notification->id,
                    'data' => $notification->data,
                ]);
                return redirect()->route('customer.notifications.index')
                    ->with('error', 'Invalid notification data format.');
            }

            $statusUpdate = StatusUpdate::findOrFail($data['status_update_id']);
            $waterSentiment = WaterSentiment::findOrFail($data['water_sentiment_id']);

            if ($request->response === 'confirmed') {
                $statusUpdate->update(['status' => 'confirmed']);
                $waterSentiment->update([
                    'status' => $statusUpdate->new_status,
                    'pending_status_update_id' => null,
                    'resolved_at' => $statusUpdate->new_status === 'resolved' ? now() : null,
                    'closed_at' => $statusUpdate->new_status === 'closed' ? now() : null,
                ]);
                Log::info('Notification confirmed', [
                    'notification_id' => $notification->id,
                    'water_sentiment_id' => $waterSentiment->id,
                    'user_id' => Auth::id(),
                ]);
            } else {
                $statusUpdate->update([
                    'status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason,
                ]);
                $waterSentiment->update(['pending_status_update_id' => null]);
                Log::info('Notification rejected', [
                    'notification_id' => $notification->id,
                    'water_sentiment_id' => $waterSentiment->id,
                    'user_id' => Auth::id(),
                    'rejection_reason' => $request->rejection_reason,
                ]);
            }

            $notification->update([
                'read_at' => now(),
                'action_required' => false,
            ]);

            return redirect()->route('customer.notifications.index')
                ->with('success', 'Response recorded successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to process notification response', [
                'notification_id' => $notification->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('customer.notifications.index')
                ->with('error', 'Failed to record response. Please try again.');
        }
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            Log::warning('Unauthorized mark as read attempt', [
                'user_id' => Auth::id(),
                'notification_id' => $notification->id,
            ]);
            abort(403, 'Unauthorized action.');
        }

        try {
            $notification->update(['read_at' => now()]);
            Log::info('Notification marked as read', [
                'notification_id' => $notification->id,
                'user_id' => Auth::id(),
            ]);
            return redirect()->route('customer.notifications.index')
                ->with('success', 'Notification marked as read.');
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notification->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('customer.notifications.index')
                ->with('error', 'Failed to mark as read. Please try again.');
        }
    }

    public function getNotificationCount()
    {
        $pendingConfirmations = Notification::where('user_id', Auth::id())
            ->where('type', 'status_confirmation_required')
            ->where('action_required', true)
            ->whereNull('read_at')
            ->count();

        $unread = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        Log::info('Notification count fetched', [
            'user_id' => Auth::id(),
            'pending_confirmations' => $pendingConfirmations,
            'unread' => $unread,
        ]);

        return response()->json([
            'pending_confirmations' => $pendingConfirmations,
            'unread' => $unread,
        ]);
    }
}