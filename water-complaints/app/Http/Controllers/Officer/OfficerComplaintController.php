<?php
namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\WaterSentiment;
use App\Models\Notification;
use App\Models\StatusUpdate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class OfficerComplaintController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $complaints = WaterSentiment::where('assigned_to', $user->id)
            ->with('user', 'assignedOfficer')
            ->orderBy('timestamp', 'desc')
            ->get();

        $stats = [
            'total' => $complaints->count(),
            'pending' => $complaints->where('status', 'pending')->count(),
            'in_progress' => $complaints->where('status', 'in_progress')->count(),
            'resolved' => $complaints->where('status', 'resolved')->count(),
        ];

        Log::info('Officer dashboard loaded', [
            'user_id' => $user->id,
            'complaint_count' => $complaints->count(),
            'complaint_ids' => $complaints->pluck('id')->toArray(),
        ]);

        return view('officer.index', compact('complaints', 'stats'));
    }

    public function show($id)
    {
        $waterSentiment = WaterSentiment::with('user', 'assignedOfficer', 'statusUpdates')->findOrFail($id);
        $statusOptions = $this->getAvailableStatusOptions($waterSentiment);

        return view('officer.show', compact('waterSentiment', 'statusOptions'));
    }

    public function notifications()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->whereIn('type', ['customer_response'])
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

        return view('officer.notifications.index', compact('notifications'));
    }

    public function getNotificationCount()
    {
        $unread = Notification::where('user_id', Auth::id())
            ->where('type', 'customer_response')
            ->whereNull('read_at')
            ->count();

        Log::info('Officer notification count fetched', [
            'user_id' => Auth::id(),
            'unread' => $unread,
        ]);

        return response()->json(['unread' => $unread]);
    }

    public function getNotificationsList()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->where('type', 'customer_response')
            ->latest()
            ->take(5) // Limit to 5 for modal
            ->get();

        $notifications->transform(function ($notification) {
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

        $html = view('officer.notifications.partial', compact('notifications'))->render();

        return response()->json(['html' => $html]);
    }

    public function respondToCustomer(Request $request, Notification $notification)
    {
        $request->validate([
            'response_notes' => 'required|string|max:1000',
            'proposed_status' => 'nullable|in:resolved,closed',
        ]);

        if ($notification->user_id !== Auth::id()) {
            Log::warning('Unauthorized officer response attempt', [
                'user_id' => Auth::id(),
                'notification_id' => $notification->id,
            ]);
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);

            if (!isset($data['water_sentiment_id']) || !isset($data['customer_notification_id'])) {
                Log::error('Missing required data fields in officer notification', [
                    'notification_id' => $notification->id,
                    'data' => $data,
                ]);
                return redirect()->route('officer.notifications.index')
                    ->with('error', 'Invalid notification data.');
            }

            $waterSentiment = WaterSentiment::findOrFail($data['water_sentiment_id']);
            $customerNotification = Notification::findOrFail($data['customer_notification_id']);

            // If a new status is proposed, create a new StatusUpdate
            if ($request->proposed_status) {
                $statusUpdate = StatusUpdate::create([
                    'water_sentiment_id' => $waterSentiment->id,
                    'status' => 'pending_confirmation',
                    'new_status' => $request->proposed_status,
                    'old_status' => $waterSentiment->status,
                    'officer_notes' => $request->response_notes,
                    'officer_id' => Auth::id(),
                ]);

                $waterSentiment->update([
                    'status' => 'pending_customer_confirmation',
                    'pending_status_update_id' => $statusUpdate->id,
                    'officer_notes' => $request->response_notes,
                ]);

                // Notify customer of new proposed status
                Notification::create([
                    'user_id' => $customerNotification->user_id,
                    'type' => 'status_confirmation_required',
                    'title' => 'Updated Status for Complaint #' . $waterSentiment->id,
                    'message' => "We’ve reviewed your feedback for complaint #{$waterSentiment->id}. Based on your input, we propose marking it as " . ucfirst($request->proposed_status) . ". Notes: {$request->response_notes}. Please confirm or reject this status.",
                    'data' => [
                        'water_sentiment_id' => $waterSentiment->id,
                        'status' => $request->proposed_status,
                        'status_update_id' => $statusUpdate->id,
                    ],
                    'action_required' => true,
                    'expires_at' => now()->addDays(7),
                ]);
            } else {
                // Notify customer of officer response without status change
                Notification::create([
                    'user_id' => $customerNotification->user_id,
                    'type' => 'response_acknowledgement',
                    'title' => 'Officer Response for Complaint #' . $waterSentiment->id,
                    'message' => "Our team has reviewed your rejection for complaint #{$waterSentiment->id}. {$request->response_notes}",
                    'data' => [
                        'water_sentiment_id' => $waterSentiment->id,
                        'original_notification_id' => $notification->id,
                        'response' => 'officer_response',
                    ],
                    'action_required' => false,
                    'expires_at' => now()->addDays(7),
                ]);
            }

            $notification->update([
                'read_at' => now(),
                'action_required' => false,
            ]);

            Log::info('Officer responded to customer notification', [
                'notification_id' => $notification->id,
                'water_sentiment_id' => $waterSentiment->id,
                'officer_id' => Auth::id(),
                'response_notes' => $request->response_notes,
                'proposed_status' => $request->proposed_status,
            ]);

            DB::commit();
            return redirect()->back()
                ->with('success', 'Response submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process officer response', [
                'notification_id' => $notification->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->with('error', 'Failed to submit response. Please try again.');
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
            return redirect()->back()
                ->with('success', 'Notification marked as read.');
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notification->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()
                ->with('error', 'Failed to mark as read. Please try again.');
        }
    }

public function updateComplaintStatus(Request $request, $complaint)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,closed',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $waterSentiment = WaterSentiment::findOrFail($complaint);
            $newStatus = $request->input('status');
            $notes = $request->input('notes');

            if (in_array($newStatus, ['resolved', 'closed'])) {
                if (!$notes) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Notes are required for this status.'
                    ], 422);
                }

                $statusUpdate = StatusUpdate::create([
                    'water_sentiment_id' => $waterSentiment->id,
                    'status' => 'pending_confirmation',
                    'new_status' => $newStatus,
                    'old_status' => $waterSentiment->status,
                    'officer_notes' => $notes,
                    'officer_id' => Auth::id(),
                ]);

                if (!in_array($statusUpdate->new_status, ['resolved', 'closed'])) {
                    Log::error('Invalid new_status created', [
                        'status_update_id' => $statusUpdate->id,
                        'new_status' => $statusUpdate->new_status,
                    ]);
                    throw new \Exception('Invalid status configuration.');
                }

                $waterSentiment->status = 'pending_customer_confirmation';
                $waterSentiment->pending_status_update_id = $statusUpdate->id;
                $waterSentiment->officer_notes = $notes;
            } else {
                $waterSentiment->status = $newStatus;
                if ($notes) {
                    $waterSentiment->officer_notes = $notes;
                }
                if ($newStatus === 'in_progress') {
                    $waterSentiment->assigned_to = Auth::id();
                }
                $waterSentiment->pending_status_update_id = null;
            }

            $waterSentiment->save();

            $notificationCreated = false;
            if ($waterSentiment->user_id && in_array($newStatus, ['resolved', 'closed'])) {
                $notificationCreated = $this->createCustomerNotification($waterSentiment, $newStatus, $statusUpdate->id);
            }

            Log::info('Complaint status updated', [
                'water_sentiment_id' => $waterSentiment->id,
                'status' => $waterSentiment->status,
                'user_id' => $waterSentiment->user_id,
                'officer_notes' => $notes,
                'pending_status_update_id' => $waterSentiment->pending_status_update_id,
                'notification_created' => $notificationCreated,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'status' => $waterSentiment->status,
                'requires_confirmation' => in_array($newStatus, ['resolved', 'closed']),
                'notification_created' => $notificationCreated,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update complaint status', [
                'water_sentiment_id' => $complaint,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAvailableStatusOptions($complaint)
    {
        $waterSentiment = $complaint instanceof WaterSentiment ? $complaint : WaterSentiment::findOrFail($complaint);
        $options = [
            'pending' => '📋 Pending',
            'in_progress' => '⚡ In Progress',
            'resolved' => '✅ Resolved',
            'closed' => '🔒 Closed',
        ];

        if ($waterSentiment->status === 'pending_customer_confirmation') {
            unset($options['resolved'], $options['closed']);
        }

        return $options;
    }

    protected function createCustomerNotification(WaterSentiment $complaint, $status, $statusUpdateId)
    {
        if (!$complaint->user_id) {
            Log::error('Cannot create notification: user_id is null', [
                'water_sentiment_id' => $complaint->id,
                'status' => $status,
                'status_update_id' => $statusUpdateId,
            ]);
            return false;
        }

        $user = User::find($complaint->user_id);
        if (!$user) {
            Log::error('Cannot create notification: user does not exist', [
                'water_sentiment_id' => $complaint->id,
                'user_id' => $complaint->user_id,
                'status' => $status,
                'status_update_id' => $statusUpdateId,
            ]);
            return false;
        }

        try {
            $notificationData = [
                'user_id' => $complaint->user_id,
                'type' => 'status_confirmation_required',
                'title' => 'Complaint Status Update',
                'message' => "Your complaint #{$complaint->id} has been marked as " . ucfirst($status) . ". Please confirm or reject this status.",
                'data' => [
                    'water_sentiment_id' => $complaint->id,
                    'status' => $status,
                    'status_update_id' => $statusUpdateId,
                ],
                'action_required' => true,
                'expires_at' => now()->addDays(7),
            ];

            $notification = Notification::create($notificationData);

            Log::info('Customer notification created successfully', [
                'notification_id' => $notification->id,
                'complaint_id' => $complaint->id,
                'user_id' => $complaint->user_id,
                'status' => $status,
                'status_update_id' => $statusUpdateId,
                'notification_data' => $notificationData,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to create customer notification', [
                'water_sentiment_id' => $complaint->id,
                'user_id' => $complaint->user_id,
                'status' => $status,
                'status_update_id' => $statusUpdateId,
                'notification_data' => $notificationData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}