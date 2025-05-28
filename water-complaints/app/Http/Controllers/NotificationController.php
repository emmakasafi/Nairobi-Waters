<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\WaterSentiment;
use App\Models\StatusUpdate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $notification->update(['read_at' => now()]);
        return back()->with('success', 'Notification marked as read.');
    }

    public function respond(Request $request, Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($notification->type !== 'status_confirmation_required' || !$notification->action_required) {
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
            return back()->with('error', 'Invalid complaint or status update.');
        }

        DB::transaction(function () use ($notification, $waterSentiment, $statusUpdate, $request) {
            if ($request->response === 'confirmed') {
                // Update StatusUpdate
                $statusUpdate->update([
                    'status' => 'confirmed',
                    'customer_confirmed_at' => now(),
                ]);

                // Update WaterSentiment
                $waterSentiment->update([
                    'status' => $statusUpdate->new_status,
                    'pending_status_update_id' => null,
                    'resolved_at' => $statusUpdate->new_status === 'resolved' ? now() : null,
                    'closed_at' => $statusUpdate->new_status === 'closed' ? now() : null,
                ]);

                // Mark notification as read
                $notification->update([
                    'action_required' => false,
                    'read_at' => now(),
                ]);

                // Notify officer
                $this->createOfficerNotification($waterSentiment, $statusUpdate, 'confirmed');
            } else {
                // Update StatusUpdate
                $statusUpdate->update([
                    'status' => 'rejected',
                    'customer_rejection_reason' => $request->rejection_reason,
                    'customer_responded_at' => now(),
                ]);

                // Revert WaterSentiment to previous status
                $waterSentiment->update([
                    'status' => $statusUpdate->old_status,
                    'pending_status_update_id' => null,
                ]);

                // Mark notification as read
                $notification->update([
                    'action_required' => false,
                    'read_at' => now(),
                ]);

                // Notify officer
                $this->createOfficerNotification($waterSentiment, $statusUpdate, 'rejected');
            }
        });

        $message = $request->response === 'confirmed'
            ? 'Status change confirmed successfully.'
            : 'Status change rejected. Officer has been notified.';

        return redirect()->route('customer.notifications.index')
            ->with('success', $message);
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

        Notification::create([
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
    }
}