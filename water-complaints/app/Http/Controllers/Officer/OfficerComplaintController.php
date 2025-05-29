<?php
namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\WaterSentiment;
use App\Models\Notification;
use App\Models\StatusUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        Log::info('Officer dashboard loaded', [
            'user_id' => $user->id,
            'complaint_count' => $complaints->count(),
            'complaint_ids' => $complaints->pluck('id')->toArray(),
        ]);

        return view('officer.index', compact('complaints'));
    }

    public function show($id)
    {
        $waterSentiment = WaterSentiment::with('user', 'assignedOfficer', 'statusUpdates')->findOrFail($id);
        $statusOptions = $this->getAvailableStatusOptions($waterSentiment);

        return view('officer.show', compact('waterSentiment', 'statusOptions'));
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
                $notificationCreated = $this->createCustomerNotification($waterSentiment, $newStatus);
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

    protected function createCustomerNotification(WaterSentiment $complaint, $status)
    {
        if (!$complaint->user_id) {
            Log::error('Cannot create notification: user_id is null', [
                'water_sentiment_id' => $complaint->id,
                'status' => $status,
            ]);
            return false;
        }

        try {
            $notification = Notification::create([
                'user_id' => $complaint->user_id,
                'type' => 'status_confirmation_required',
                'title' => 'Complaint Status Update',
                'message' => "Your complaint #{$complaint->id} has been marked as " . ucfirst($status) . ". Please confirm or reject this status.",
                'data' => [
                    'water_sentiment_id' => $complaint->id,
                    'status' => $status,
                ],
                'action_required' => true,
                'expires_at' => now()->addDays(7),
            ]);

            Log::info('Customer notification created successfully', [
                'notification_id' => $notification->id,
                'complaint_id' => $complaint->id,
                'user_id' => $complaint->user_id,
                'status' => $status,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to create customer notification', [
                'water_sentiment_id' => $complaint->id,
                'user_id' => $complaint->user_id,
                'status' => $status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}