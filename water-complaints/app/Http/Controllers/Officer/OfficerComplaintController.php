<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Complaint;
use App\Models\StatusUpdate;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OfficerComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::where('assigned_to', Auth::id())
                              ->with(['user', 'statusUpdates'])
                              ->orderBy('created_at', 'desc')
                              ->get();
        
        return view('officer.index', compact('complaints'));
    }

    public function show(Complaint $complaint)
    {
        if ($complaint->assigned_to !== Auth::id()) {
            abort(403, 'You are not authorized to view this complaint.');
        }

        $complaint->load(['user', 'statusUpdates.officer']);
        
        return view('officer.show', compact('complaint'));
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        if ($complaint->assigned_to !== Auth::id()) {
            abort(403, 'You are not authorized to update this complaint.');
        }

        $request->validate([
            'status' => 'required|in:in_progress,pending_customer_confirmation,resolved,closed',
            'notes' => 'nullable|string|max:1000',
        ]);

        $newStatus = $request->status;
        $oldStatus = $complaint->status;
        
        // Don't allow certain status changes without customer confirmation
        if ($this->requiresCustomerConfirmation($newStatus, $oldStatus)) {
            return $this->handleStatusRequiringConfirmation($request, $complaint);
        }

        return $this->updateComplaintStatus($request, $complaint);
    }

    private function requiresCustomerConfirmation($newStatus, $oldStatus)
    {
        // These status changes require customer confirmation
        $requiresConfirmation = [
            'resolved' => true,
            'closed' => false, // Only if coming from resolved
        ];

        if ($newStatus === 'resolved') {
            return true;
        }

        if ($newStatus === 'closed' && $oldStatus !== 'resolved') {
            return true;
        }

        return false;
    }

    private function handleStatusRequiringConfirmation(Request $request, Complaint $complaint)
    {
        DB::transaction(function () use ($request, $complaint) {
            // Create a status update record for tracking
            $statusUpdate = StatusUpdate::create([
                'complaint_id' => $complaint->id,
                'officer_id' => Auth::id(),
                'old_status' => $complaint->status,
                'new_status' => $request->status,
                'officer_notes' => $request->notes,
                'requires_customer_confirmation' => true,
                'status' => 'pending_confirmation'
            ]);

            // Update complaint to pending customer confirmation
            $complaint->update([
                'status' => 'pending_customer_confirmation',
                'officer_notes' => $request->notes,
                'pending_status_update_id' => $statusUpdate->id
            ]);

            // Create notification for customer
            $this->createCustomerNotification($complaint, $statusUpdate);
        });

        return redirect()->route('officer.complaints.show', $complaint)
                        ->with('success', 'Status update sent to customer for confirmation. You will be notified once they respond.');
    }

    private function updateComplaintStatus(Request $request, Complaint $complaint)
    {
        $oldStatus = $complaint->status;
        
        DB::transaction(function () use ($request, $complaint, $oldStatus) {
            // Create status update record
            StatusUpdate::create([
                'complaint_id' => $complaint->id,
                'officer_id' => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'officer_notes' => $request->notes,
                'requires_customer_confirmation' => false,
                'status' => 'completed'
            ]);

            // Update complaint
            $complaint->update([
                'status' => $request->status,
                'officer_notes' => $request->notes,
                'resolved_at' => $request->status === 'resolved' ? now() : null,
                'closed_at' => $request->status === 'closed' ? now() : null,
            ]);

            // Create notification for customer about direct status change
            $this->createStatusChangeNotification($complaint, $oldStatus, $request->status);
        });

        $statusLabel = ucfirst(str_replace('_', ' ', $request->status));
        return redirect()->route('officer.complaints.show', $complaint)
                        ->with('success', "Complaint status updated to {$statusLabel} successfully.");
    }

    public function handleCustomerResponse(Request $request, Complaint $complaint)
    {
        if ($complaint->assigned_to !== Auth::id()) {
            abort(403);
        }

        $statusUpdate = StatusUpdate::find($complaint->pending_status_update_id);
        
        if (!$statusUpdate || $statusUpdate->status !== 'pending_confirmation') {
            return redirect()->route('officer.complaints.show', $complaint)
                           ->with('error', 'No pending status update found.');
        }

        $customerResponse = $request->input('customer_response'); // 'confirmed' or 'rejected'
        
        if ($customerResponse === 'confirmed') {
            return $this->processConfirmedStatus($complaint, $statusUpdate);
        } else {
            return $this->processRejectedStatus($complaint, $statusUpdate, $request);
        }
    }

    private function processConfirmedStatus(Complaint $complaint, StatusUpdate $statusUpdate)
    {
        DB::transaction(function () use ($complaint, $statusUpdate) {
            // Update the status update record
            $statusUpdate->update([
                'status' => 'confirmed',
                'customer_confirmed_at' => now()
            ]);

            // Update complaint to the confirmed status
            $complaint->update([
                'status' => $statusUpdate->new_status,
                'pending_status_update_id' => null,
                'resolved_at' => $statusUpdate->new_status === 'resolved' ? now() : null,
                'closed_at' => $statusUpdate->new_status === 'closed' ? now() : null,
            ]);

            // Create notification for officer
            $this->createOfficerNotification($complaint, $statusUpdate, 'confirmed');
        });

        $statusLabel = ucfirst(str_replace('_', ' ', $statusUpdate->new_status));
        return redirect()->route('officer.complaints.show', $complaint)
                        ->with('success', "Customer confirmed the status change. Complaint is now {$statusLabel}.");
    }

    private function processRejectedStatus(Complaint $complaint, StatusUpdate $statusUpdate, Request $request)
    {
        DB::transaction(function () use ($complaint, $statusUpdate, $request) {
            // Update the status update record
            $statusUpdate->update([
                'status' => 'rejected',
                'customer_rejection_reason' => $request->input('rejection_reason'),
                'customer_responded_at' => now()
            ]);

            // Revert complaint to previous status
            $complaint->update([
                'status' => $statusUpdate->old_status,
                'pending_status_update_id' => null
            ]);

            // Create notification for officer
            $this->createOfficerNotification($complaint, $statusUpdate, 'rejected');
        });

        return redirect()->route('officer.complaints.show', $complaint)
                        ->with('warning', 'Customer rejected the status change. Please review their feedback and take appropriate action.');
    }

    private function createCustomerNotification(Complaint $complaint, StatusUpdate $statusUpdate)
    {
        $statusLabel = ucfirst(str_replace('_', ' ', $statusUpdate->new_status));
        
        Notification::create([
            'user_id' => $complaint->user_id,
            'type' => 'status_confirmation_required',
            'title' => 'Complaint Status Update Confirmation Required',
            'message' => "Your complaint #{$complaint->id} has been marked as '{$statusLabel}' by the assigned officer. Please confirm if you agree with this status change.",
            'data' => json_encode([
                'complaint_id' => $complaint->id,
                'status_update_id' => $statusUpdate->id,
                'new_status' => $statusUpdate->new_status,
                'officer_notes' => $statusUpdate->officer_notes
            ]),
            'action_required' => true,
            'expires_at' => now()->addDays(7) // Give customer 7 days to respond
        ]);
    }

    private function createStatusChangeNotification(Complaint $complaint, $oldStatus, $newStatus)
    {
        $oldLabel = ucfirst(str_replace('_', ' ', $oldStatus));
        $newLabel = ucfirst(str_replace('_', ' ', $newStatus));
        
        Notification::create([
            'user_id' => $complaint->user_id,
            'type' => 'status_changed',
            'title' => 'Complaint Status Updated',
            'message' => "Your complaint #{$complaint->id} status has been changed from '{$oldLabel}' to '{$newLabel}'.",
            'data' => json_encode([
                'complaint_id' => $complaint->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ])
        ]);
    }

    private function createOfficerNotification(Complaint $complaint, StatusUpdate $statusUpdate, $responseType)
    {
        $statusLabel = ucfirst(str_replace('_', ' ', $statusUpdate->new_status));
        
        if ($responseType === 'confirmed') {
            $message = "Customer has confirmed the status change for complaint #{$complaint->id}. Status is now '{$statusLabel}'.";
            $title = 'Customer Confirmed Status Change';
        } else {
            $message = "Customer has rejected the status change for complaint #{$complaint->id}. Please review their feedback.";
            $title = 'Customer Rejected Status Change';
        }

        Notification::create([
            'user_id' => Auth::id(),
            'type' => 'customer_response',
            'title' => $title,
            'message' => $message,
            'data' => json_encode([
                'complaint_id' => $complaint->id,
                'status_update_id' => $statusUpdate->id,
                'response_type' => $responseType
            ])
        ]);
    }

    public function getAvailableStatusOptions(Complaint $complaint)
    {
        $currentStatus = $complaint->status;
        $options = [];

        switch ($currentStatus) {
            case 'pending':
                $options = [
                    'in_progress' => '⚡ In Progress',
                    'resolved' => '✅ Resolved (Requires Customer Confirmation)',
                    'closed' => '🔒 Closed'
                ];
                break;
                
            case 'in_progress':
                $options = [
                    'resolved' => '✅ Resolved (Requires Customer Confirmation)',
                    'pending' => '📋 Back to Pending'
                ];
                break;
                
            case 'pending_customer_confirmation':
                $options = [
                    'in_progress' => '⚡ Back to In Progress'
                ];
                break;
                
            case 'resolved':
                $options = [
                    'closed' => '🔒 Close Case',
                    'in_progress' => '⚡ Reopen (Back to In Progress)'
                ];
                break;
        }

        return $options;
    }
}