<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\WaterSentiment;
use App\Models\StatusUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        $notifications->getCollection()->transform(function ($notification) {
            $notification->complaint_data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);

            if (isset($notification->complaint_data['water_sentiment_id'])) {
                $notification->waterSentiment = WaterSentiment::with(['statusUpdates' => function ($query) {
                    $query->latest()->limit(1);
                }])
                    ->where('id', $notification->complaint_data['water_sentiment_id'])
                    ->first();
            }

            return $notification;
        });

        return view('customer.notifications.index', compact('notifications'));
    }

    public function respond(Request $request, Notification $notification)
{
    $request->validate([
        'response' => 'required|in:confirmed,rejected',
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
        DB::beginTransaction();

        $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);

        if (!is_array($data) || !isset($data['status_update_id']) || !isset($data['water_sentiment_id'])) {
            Log::error('Invalid or missing notification data', [
                'notification_id' => $notification->id,
                'data' => $notification->data,
            ]);
            throw new \Exception('Invalid notification data format.');
        }

        $statusUpdate = StatusUpdate::findOrFail($data['status_update_id']);
        $waterSentiment = WaterSentiment::findOrFail($data['water_sentiment_id']);

        Log::debug('Processing response', [
            'notification_id' => $notification->id,
            'status_update' => [
                'id' => $statusUpdate->id,
                'status' => $statusUpdate->status,
                'new_status' => $statusUpdate->new_status,
            ],
            'water_sentiment' => [
                'id' => $waterSentiment->id,
                'status' => $waterSentiment->status,
                'pending_status_update_id' => $waterSentiment->pending_status_update_id,
                'assigned_to' => $waterSentiment->assigned_to,
            ],
            'response' => $request->response,
        ]);

        $customerMessage = '';
        $officerMessage = '';
        $updateData = [
            'status' => $request->response === 'confirmed' ? $statusUpdate->new_status : 'in_progress',
            'pending_status_update_id' => null,
        ];

        if ($request->response === 'confirmed') {
            if (!in_array($statusUpdate->new_status, ['resolved', 'closed'])) {
                Log::error('Invalid new_status for confirmation', [
                    'notification_id' => $notification->id,
                    'status_update_id' => $statusUpdate->id,
                    'new_status' => $statusUpdate->new_status,
                ]);
                throw new \Exception('Status update is not for resolved or closed.');
            }

            $statusUpdate->update(['status' => 'confirmed']);

            // Only set timestamps if columns exist
            if (Schema::hasColumn('water_sentiments', 'resolved_at')) {
                $updateData['resolved_at'] = $statusUpdate->new_status === 'resolved' ? now() : $waterSentiment->resolved_at;
            }
            if (Schema::hasColumn('water_sentiments', 'closed_at')) {
                $updateData['closed_at'] = $statusUpdate->new_status === 'closed' ? now() : $waterSentiment->closed_at;
            }

            $waterSentiment->update($updateData);

            $customerMessage = "Thank you for confirming the {$statusUpdate->new_status} status of your complaint #{$waterSentiment->id}. ";
            $customerMessage .= $statusUpdate->new_status === 'resolved'
                ? "We’re glad your issue has been resolved."
                : "Your complaint is now closed.";
            $officerMessage = "Customer confirmed the {$statusUpdate->new_status} status for complaint #{$waterSentiment->id}.";
        } else {
            $statusUpdateData = ['status' => 'rejected'];
            if (Schema::hasColumn('status_updates', 'rejection_reason')) {
                $statusUpdateData['rejection_reason'] = $request->rejection_reason;
            }
            $statusUpdate->update($statusUpdateData);

            $waterSentiment->update($updateData);

            $rejectionReason = $request->rejection_reason ?? 'No reason provided';
            $customerMessage = "We’ve received your rejection for complaint #{$waterSentiment->id}. Reason: '{$rejectionReason}'. Our team will review and take action.";
            $officerMessage = "Customer rejected the {$statusUpdate->new_status} status for complaint #{$waterSentiment->id}. Reason: '{$rejectionReason}'.";
        }

        $notification->update([
            'read_at' => now(),
            'action_required' => false,
        ]);

        Notification::create([
            'user_id' => $notification->user_id,
            'type' => 'response_acknowledgement',
            'title' => 'Response Received for Complaint #' . $waterSentiment->id,
            'message' => $customerMessage,
            'data' => [
                'water_sentiment_id' => $waterSentiment->id,
                'original_notification_id' => $notification->id,
                'response' => $request->response,
            ],
            'action_required' => false,
            'expires_at' => now()->addDays(7),
        ]);

        if ($waterSentiment->assigned_to) {
            Notification::create([
                'user_id' => $waterSentiment->assigned_to,
                'type' => 'customer_response',
                'title' => 'Customer Response for Complaint #' . $waterSentiment->id,
                'message' => $officerMessage,
                'data' => [
                    'water_sentiment_id' => $waterSentiment->id,
                    'customer_notification_id' => $notification->id,
                    'response' => $request->response,
                    'rejection_reason' => $request->rejection_reason ?? null,
                ],
                'action_required' => $request->response === 'rejected',
                'expires_at' => now()->addDays(7),
            ]);
        }

        DB::commit();
        Log::info('Successfully processed notification response', [
            'notification_id' => $notification->id,
            'user_id' => Auth::id(),
            'response' => $request->response,
            'water_sentiment_id' => $waterSentiment->id,
        ]);
        return redirect()->route('customer.notifications.index')
            ->with('success', 'Response recorded successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to process notification response', [
            'notification_id' => $notification->id,
            'user_id' => Auth::id(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'data' => $data ?? null,
            'status_update_id' => $data['status_update_id'] ?? null,
            'water_sentiment_id' => $data['water_sentiment_id'] ?? null,
        ]);
        return redirect()->back()
            ->with('error', 'Failed to record response: ' . $e->getMessage());
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