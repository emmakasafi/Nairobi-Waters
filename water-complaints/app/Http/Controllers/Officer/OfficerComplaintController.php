<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WaterSentiment;
use App\Models\StatusUpdate;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfficerComplaintController extends Controller
{
    public function index()
    {
        $stats = [
            'total' => WaterSentiment::where('assigned_to', Auth::id())->count(),
            'pending' => WaterSentiment::where('assigned_to', Auth::id())->where('status', 'pending')->count(),
            'in_progress' => WaterSentiment::where('assigned_to', Auth::id())->where('status', 'in_progress')->count(),
            'resolved' => WaterSentiment::where('assigned_to', Auth::id())->where('status', 'resolved')->count(),
        ];

        $waterSentiments = WaterSentiment::where('assigned_to', Auth::id())
            ->with(['user', 'statusUpdates'])
            ->when(request('status'), function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when(request('sentiment'), function ($query, $sentiment) {
                return $query->where('overall_sentiment', $sentiment);
            })
            ->when(request('date_from'), function ($query, $dateFrom) {
                return $query->whereDate('timestamp', '>=', $dateFrom);
            })
            ->when(request('date_to'), function ($query, $dateTo) {
                return $query->whereDate('timestamp', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('officer.officer.index', compact('waterSentiments', 'stats'));
    }

    public function show(WaterSentiment $complaint)
    {
        // Log entry to the method
        Log::info('Entering show method', [
            'complaint_id' => $complaint->id,
            'officer_id' => Auth::id(),
        ]);

        // Check authorization
        if ($complaint->assigned_to !== Auth::id()) {
            Log::warning('Unauthorized access attempt', [
                'complaint_id' => $complaint->id,
                'officer_id' => Auth::id(),
                'assigned_to' => $complaint->assigned_to,
            ]);
            abort(403, 'You are not authorized to view this complaint.');
        }

        // Load relationships
        $complaint->load(['user', 'statusUpdates.officer']);

        // Get status options
        $statusOptions = $this->getAvailableStatusOptions($complaint);

        // Log status options
        Log::info('Status options generated', [
            'complaint_id' => $complaint->id,
            'status' => $complaint->status,
            'statusOptions' => $statusOptions,
        ]);

        // Pass data to view
        return view('officer.show', [
            'waterSentiment' => $complaint,
            'statusOptions' => $statusOptions,
        ]);
    }

    public function updateComplaintStatus(Request $request, WaterSentiment $complaint)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,closed',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $complaint->status;

        if (!$complaint->id || !Auth::id()) {
            Log::error('Invalid complaint ID or officer not authenticated', [
                'complaint_id' => $complaint->id,
                'officer_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid complaint or authentication issue.',
            ], 400);
        }

        $requiresCustomerConfirmation = $this->requiresCustomerConfirmation($request->status, $oldStatus);

        try {
            DB::beginTransaction();

            $statusUpdate = StatusUpdate::create([
                'water_sentiment_id' => $complaint->id,
                'officer_id' => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'officer_notes' => $request->notes,
                'requires_customer_confirmation' => $requiresCustomerConfirmation,
                'status' => $requiresCustomerConfirmation ? 'pending_confirmation' : 'completed',
            ]);

            if ($requiresCustomerConfirmation) {
                $complaint->update([
                    'status' => 'pending_customer_confirmation',
                    'officer_notes' => $request->notes,
                    'pending_status_update_id' => $statusUpdate->id,
                ]);

                $this->createCustomerNotification($complaint, $statusUpdate);
            } else {
                $complaint->update([
                    'status' => $request->status,
                    'officer_notes' => $request->notes,
                    'resolved_at' => $request->status === 'resolved' ? now() : null,
                    'closed_at' => $request->status === 'closed' ? now() : null,
                ]);

                $this->createStatusChangeNotification($complaint, $oldStatus, $request->status);
            }

            DB::commit();

            $statusLabel = ucfirst(str_replace('_', ' ', $request->status));
            return response()->json([
                'success' => true,
                'message' => $requiresCustomerConfirmation
                    ? 'Status update sent to customer for confirmation.'
                    : "Complaint status updated to {$statusLabel} successfully.",
                'status' => $requiresCustomerConfirmation ? 'pending_customer_confirmation' : $request->status,
                'requires_confirmation' => $requiresCustomerConfirmation,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update complaint status: ' . $e->getMessage(), [
                'complaint_id' => $complaint->id,
                'officer_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function requiresCustomerConfirmation($newStatus, $oldStatus)
    {
        return $newStatus === 'resolved' || ($newStatus === 'closed' && $oldStatus !== 'resolved');
    }

    private function createCustomerNotification(WaterSentiment $complaint, StatusUpdate $statusUpdate)
{
    if (!$complaint->user_id) {
        Log::error('Cannot create notification: user_id is null', [
            'complaint_id' => $complaint->id,
            'status_update_id' => $statusUpdate->id,
        ]);
        return;
    }

    $statusLabel = ucfirst(str_replace('_', ' ', $statusUpdate->new_status));

    Notification::create([
        'user_id' => $complaint->user_id,
        'type' => 'status_confirmation_required',
        'title' => 'Complaint Status Update Confirmation Required',
        'message' => "Your complaint #{$complaint->id} has been marked as '{$statusLabel}' by the assigned officer. Please confirm if you agree with this status change.",
        'data' => json_encode([
            'water_sentiment_id' => $complaint->id,
            'status_update_id' => $statusUpdate->id,
            'new_status' => $statusUpdate->new_status,
            'officer_notes' => $statusUpdate->officer_notes,
        ]),
        'action_required' => true,
        'expires_at' => now()->addDays(7),
    ]);
}

private function createStatusChangeNotification(WaterSentiment $complaint, $oldStatus, $newStatus)
{
    if (!$complaint->user_id) {
        Log::error('Cannot create notification: user_id is null', [
            'complaint_id' => $complaint->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
        return;
    }

    $oldLabel = ucfirst(str_replace('_', ' ', $oldStatus));
    $newLabel = ucfirst(str_replace('_', ' ', $newStatus));

    Notification::create([
        'user_id' => $complaint->user_id,
        'type' => 'status_changed',
        'title' => 'Complaint Status Updated',
        'message' => "Your complaint #{$complaint->id} status has been changed from '{$oldLabel}' to '{$newLabel}'.",
        'data' => json_encode([
            'water_sentiment_id' => $complaint->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]),
    ]);
}

    public function getAvailableStatusOptions(WaterSentiment $complaint)
    {
        // Define all possible status options
        $allStatuses = [
            'pending' => '📋 Pending',
            'in_progress' => '⚡ In Progress',
            'pending_customer_confirmation' => '⏳ Pending Customer Confirmation',
            'resolved' => '✅ Resolved (Requires Customer Confirmation)',
            'closed' => '🔒 Closed (Requires Customer Confirmation)',
        ];

        // Current status
        $currentStatus = $complaint->status ?? 'pending';

        // Start with all statuses
        $options = $allStatuses;

        // Mark current status
        if (array_key_exists($currentStatus, $allStatuses)) {
            $options[$currentStatus] = $allStatuses[$currentStatus] . ' (Current)';
        } else {
            // Handle unexpected status
            $options[$currentStatus] = ucfirst(str_replace('_', ' ', $currentStatus)) . ' (Current)';
        }

        // Log the options for debugging
        Log::debug('Generated status options', [
            'complaint_id' => $complaint->id,
            'current_status' => $currentStatus,
            'options' => $options,
        ]);

        return $options;
    }

    public function getStatusOptions(WaterSentiment $complaint)
    {
        return response()->json($this->getAvailableStatusOptions($complaint));
    }
}