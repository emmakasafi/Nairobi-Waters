<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\WaterSentiment;
use App\Models\StatusUpdate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function getNotificationCount()
    {
        $user = auth()->user();
        $pendingConfirmations = Notification::where('user_id', $user->id)
            ->where('type', 'status_confirmation_required')
            ->where('action_required', true)
            ->where('expires_at', '>', now())
            ->count();

        $unread = Notification::where('user_id', $user->id)
            ->where('read_at', null)
            ->where('expires_at', '>', now())
            ->count();

        Log::info('Notification count fetched', [
            'user_id' => $user->id,
            'pending_confirmations' => $pendingConfirmations,
            'unread' => $unread,
        ]);

        return response()->json([
            'pending_confirmations' => $pendingConfirmations,
            'unread' => $unread,
        ]);
    }

    public function index()
    {
        $user = auth()->user();
        $notifications = Notification::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        Log::info('Notifications fetched for index', [
            'user_id' => $user->id,
            'notification_count' => $notifications->total(),
        ]);

        return view('customer.notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to mark notification as read', [
                'notification_id' => $notification->id,
                'user_id' => Auth::id(),
            ]);
            abort(403, 'Unauthorized action.');
        }

        $notification->update(['read_at' => now()]);
        Log::info('Notification marked as read', [
            'notification_id' => $notification->id,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function respond(Request $request, Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to respond to notification', [
                'notification_id' => $notification->id,
                'user_id' => Auth::id(),
            ]);
            abort(403, 'Unauthorized action.');
        }

        if ($notification->type !== 'status_confirmation_required' || !$notification->action_required) {
            Log::error('Invalid notification type or no action required', [
                'notification_id' => $notification->id,
                'user_id' => Auth::id(),
            ]);
            return back()->with('error', 'Invalid notification type or no action required.');
        }

        $request->validate([
            'response' => 'required|in:confirmed,rejected',
            'rejection_reason' => 'required_if:response,rejected|string|max:1000|nullable',
        ]);

        $data = json_decode($notification->data, true);
        $waterSentiment = WaterSentiment::find($data['water_sentiment_id']);
        $statusUpdate = StatusUpdate::find($data['status_update_id']);

        if (!$waterSentiment || !$statusUpdate) {
            Log::error('Invalid complaint or status update', [
                'notification_id' => $notification->id,
                'water_sentiment_id' => $data['water_sentiment_id'] ?? null,
                'status_update_id' => $data['status_update_id'] ?? null,
            ]);
            return back()->with('error', 'Invalid complaint or status update.');
        }

        try {
            DB::beginTransaction();

            if ($request->response === 'confirmed') {
                $statusUpdate->update([
                    'status' => 'confirmed',
                    'customer_confirmed_at' => now(),
                ]);

                $waterSentiment->update([
                    'status' => $statusUpdate->new_status,
                    'pending_status_update_id' => null,
                    'resolved_at' => $statusUpdate->new_status === 'resolved' ? now() : null,
                    'closed_at' => $statusUpdate->new_status === 'closed' ? now() : null,
                ]);

                $notification->update([
                    'action_required' => false,
                    'read_at' => now(),
                ]);

                $this->createOfficerNotification($waterSentiment, $statusUpdate, 'confirmed');
            } else {
                $statusUpdate->update([
                    'status' => 'rejected',
                    'customer_rejection_reason' => $request->rejection_reason,
                    'customer_responded_at' => now(),
                ]);

                $waterSentiment->update([
                    'status' => $statusUpdate->old_status,
                    'pending_status_update_id' => null,
                ]);

                $notification->update([
                    'action_required' => false,
                    'read_at' => now(),
                ]);

                $this->createOfficerNotification($waterSentiment, $statusUpdate, 'rejected');
            }

            DB::commit();

            $message = $request->response === 'confirmed'
                ? 'Status change confirmed successfully.'
                : 'Status change rejected. Officer has been notified.';

            Log::info('Notification response processed', [
                'notification_id' => $notification->id,
                'user_id' => Auth::id(),
                'response' => $request->response,
                'water_sentiment_id' => $waterSentiment->id,
            ]);

            return redirect()->route('customer.notifications.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process notification response: ' . $e->getMessage(), [
                'notification_id' => $notification->id,
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to process response: ' . $e->getMessage());
        }
    }

    private function createOfficerNotification(WaterSentiment $waterSentiment, StatusUpdate $statusUpdate, $responseType)
    {
        $statusLabel = ucfirst(str_replace('_', ' ', $statusUpdate->new_status));

        if ($responseType === 'confirmed') {
            $message = "Customer has confirmed the status change for complaint #{$waterSentiment->id}. Status is now '{$statusLabel}'.";
            $title = 'Customer Confirmed Status Change';
        } else {
            $message = "Customer has rejected the status change for complaint #{$waterSentiment->id}. Reason: {$statusUpdate->customer_rejection_reason}";
            $title = 'Customer Rejected Status Change';
        }

        $notification = Notification::create([
            'user_id' => $waterSentiment->assigned_to,
            'type' => 'customer_response',
            'title' => $title,
            'message' => $message,
            'data' => json_encode([
                'water_sentiment_id' => $waterSentiment->id,
                'status_update_id' => $statusUpdate->id,
                'response_type' => $responseType,
            ]),
        ]);

        Log::info('Officer notification created', [
            'notification_id' => $notification->id,
            'user_id' => $waterSentiment->assigned_to,
            'water_sentiment_id' => $waterSentiment->id,
            'response_type' => $responseType,
        ]);
    }
}