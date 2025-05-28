<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WaterSentiment;
use App\Models\StatusUpdate;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OfficerComplaintController extends Controller
{
    public function index()
    {
        $water_sentiments = WaterSentiment::where('assigned_to', Auth::id())
                              ->with(['user', 'statusUpdates'])
                              ->orderBy('created_at', 'desc')
                              ->get();
        
        return view('officer.index', compact('water_sentiments'));
    }

    public function show(WaterSentiment $water_sentiment)
    {
        if ($water_sentiment->assigned_to !== Auth::id()) {
            abort(403, 'You are not authorized to view this water sentiment.');
        }

        $water_sentiment->load(['user', 'statusUpdates.officer']);
        
        return view('officer.show', compact('water_sentiment'));
    }

    public function updateComplaintStatus(Request $request, WaterSentiment $water_sentiment)
{
    $request->validate([
        'status' => 'required|in:in_progress,pending_customer_confirmation,resolved,closed',
        'notes' => 'nullable|string|max:1000',
    ]);

    // Store the original status BEFORE any updates
    $oldStatus = $water_sentiment->status;

    // Debug logging - check all values
    \Log::info('=== DEBUG updateComplaintStatus ===');
    \Log::info('Water Sentiment Object:', ['water_sentiment' => $water_sentiment->toArray()]);
    \Log::info('Water Sentiment ID:', ['id' => $water_sentiment->id, 'type' => gettype($water_sentiment->id)]);
    \Log::info('Auth Check:', ['check' => Auth::check(), 'id' => Auth::id(), 'user' => Auth::user()]);
    \Log::info('Officer ID:', ['id' => Auth::id(), 'type' => gettype(Auth::id())]);
    \Log::info('Old Status:', ['status' => $oldStatus]);
    \Log::info('New Status:', ['status' => $request->status]);

    // Additional safety checks
    if (!$water_sentiment->id) {
        \Log::error('Water sentiment ID is null or empty');
        return back()->with('error', 'Invalid water sentiment.');
    }

    if (!Auth::id()) {
        \Log::error('Officer ID is null - user not authenticated');
        return back()->with('error', 'Authentication required.');
    }

    // Determine if customer confirmation is required
    $requiresCustomerConfirmation = $this->requiresCustomerConfirmation($request->status, $oldStatus);

    // Prepare data for StatusUpdate creation with explicit type casting
    $statusUpdateData = [
        'water_sentiment_id' => (int) ($water_sentiment->id),
        'officer_id' => (int) (Auth::id()),
        'old_status' => $oldStatus,
        'new_status' => $request->status,
        'officer_notes' => $request->notes,
        'requires_customer_confirmation' => $requiresCustomerConfirmation,
        'status' => $requiresCustomerConfirmation ? 'pending_confirmation' : 'completed'
    ];

    \Log::info('StatusUpdate data to be inserted:', $statusUpdateData);

    // Create a status update record FIRST with the correct old status
    try {
        \Log::info('About to create StatusUpdate with data:', $statusUpdateData);

        $statusUpdate = StatusUpdate::create($statusUpdateData);

        if ($statusUpdate) {
            \Log::info('StatusUpdate created successfully:', [
                'id' => $statusUpdate->id,
                'created_record' => $statusUpdate->toArray()
            ]);
        } else {
            \Log::error('StatusUpdate::create returned null/false');
            return back()->with('error', 'Failed to create status update - creation returned null');
        }

        // Verify the record exists in database
        $verifyRecord = StatusUpdate::find($statusUpdate->id);
        if ($verifyRecord) {
            \Log::info('StatusUpdate verified in database:', $verifyRecord->toArray());
        } else {
            \Log::error('StatusUpdate not found in database after creation');
        }

    } catch (\Exception $e) {
        \Log::error('Failed to create StatusUpdate:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'data' => $statusUpdateData
        ]);
        return back()->with('error', 'Failed to create status update: ' . $e->getMessage());
    }

    // Wrap everything in a database transaction
    try {
        DB::beginTransaction();
        \Log::info('Database transaction started');

        // Now update the water sentiment based on confirmation requirements
        if ($requiresCustomerConfirmation) {
            \Log::info('Updating water sentiment for customer confirmation');

            $updateResult = $water_sentiment->update([
                'status' => 'pending_customer_confirmation',
                'officer_notes' => $request->notes,
                'pending_status_update_id' => $statusUpdate->id
            ]);

            \Log::info('Water sentiment update result:', ['success' => $updateResult]);

            // Create notification for customer
            $this->createCustomerNotification($water_sentiment, $statusUpdate);
            \Log::info('Customer notification created');

            // Create notification for officer
            $this->createOfficerNotification($water_sentiment, $statusUpdate, 'pending_confirmation');
            \Log::info('Officer notification created');
        } else {
            \Log::info('Updating water sentiment directly');

            // Update the water sentiment status directly
            $updateResult = $water_sentiment->update([
                'status' => $request->status,
                'officer_notes' => $request->notes,
                'resolved_at' => $request->status === 'resolved' ? now() : null,
                'closed_at' => $request->status === 'closed' ? now() : null,
            ]);

            \Log::info('Water sentiment direct update result:', ['success' => $updateResult]);

            // Create notification for customer about direct status change
            $this->createStatusChangeNotification($water_sentiment, $oldStatus, $request->status);
            \Log::info('Status change notification created');
        }

        DB::commit();
        \Log::info('Database transaction committed successfully');

        return redirect()->route('officer.water_sentiments.show', $water_sentiment)
                        ->with('success', 'Water sentiment status updated successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Transaction failed:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Transaction failed: ' . $e->getMessage());
    }
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

    private function handleStatusRequiringConfirmation(Request $request, WaterSentiment $water_sentiment)
    {
        DB::transaction(function () use ($request, $water_sentiment) {
            // Create a status update record for tracking
            $statusUpdate = StatusUpdate::create([
                'water_sentiment_id' => $water_sentiment->id,
                'officer_id' => Auth::id(),
                'old_status' => $water_sentiment->status,
                'new_status' => $request->status,
                'officer_notes' => $request->notes,
                'requires_customer_confirmation' => true,
                'status' => 'pending_confirmation'
            ]);

            // Update water sentiment to pending customer confirmation
            $water_sentiment->update([
                'status' => 'pending_customer_confirmation',
                'officer_notes' => $request->notes,
                'pending_status_update_id' => $statusUpdate->id
            ]);

            // Create notification for customer
            $this->createCustomerNotification($water_sentiment, $statusUpdate);
        });

        return redirect()->route('officer.water_sentiments.show', $water_sentiment)
                        ->with('success', 'Status update sent to customer for confirmation. You will be notified once they respond.');
    }

    public function updateWaterSentimentStatus(Request $request, WaterSentiment $water_sentiment)
    {
        $oldStatus = $water_sentiment->status;
        
        DB::transaction(function () use ($request, $water_sentiment, $oldStatus) {
            // Create status update record
            StatusUpdate::create([
                'water_sentiment_id' => $water_sentiment->id,
                'officer_id' => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'officer_notes' => $request->notes,
                'requires_customer_confirmation' => false,
                'status' => 'completed'
            ]);

            // Update water sentiment
            $water_sentiment->update([
                'status' => $request->status,
                'officer_notes' => $request->notes,
                'resolved_at' => $request->status === 'resolved' ? now() : null,
                'closed_at' => $request->status === 'closed' ? now() : null,
            ]);

            // Create notification for customer about direct status change
            $this->createStatusChangeNotification($water_sentiment, $oldStatus, $request->status);
        });

        $statusLabel = ucfirst(str_replace('_', ' ', $request->status));
        return redirect()->route('officer.water_sentiments.show', $water_sentiment)
                        ->with('success', "Water sentiment status updated to {$statusLabel} successfully.");
    }

    public function handleCustomerResponse(Request $request, WaterSentiment $water_sentiment)
    {
        if ($water_sentiment->assigned_to !== Auth::id()) {
            abort(403);
        }

        $statusUpdate = StatusUpdate::find($water_sentiment->pending_status_update_id);
        
        if (!$statusUpdate || $statusUpdate->status !== 'pending_confirmation') {
            return redirect()->route('officer.water_sentiments.show', $water_sentiment)
                           ->with('error', 'No pending status update found.');
        }

        $customerResponse = $request->input('customer_response'); // 'confirmed' or 'rejected'
        
        if ($customerResponse === 'confirmed') {
            return $this->processConfirmedStatus($water_sentiment, $statusUpdate);
        } else {
            return $this->processRejectedStatus($water_sentiment, $statusUpdate, $request);
        }
    }

    private function processConfirmedStatus(WaterSentiment $water_sentiment, StatusUpdate $statusUpdate)
    {
        DB::transaction(function () use ($water_sentiment, $statusUpdate) {
            // Update the status update record
            $statusUpdate->update([
                'status' => 'confirmed',
                'customer_confirmed_at' => now()
            ]);

            // Update water sentiment to the confirmed status
            $water_sentiment->update([
                'status' => $statusUpdate->new_status,
                'pending_status_update_id' => null,
                'resolved_at' => $statusUpdate->new_status === 'resolved' ? now() : null,
                'closed_at' => $statusUpdate->new_status === 'closed' ? now() : null,
            ]);

            // Create notification for officer
            $this->createOfficerNotification($water_sentiment, $statusUpdate, 'confirmed');
        });

        $statusLabel = ucfirst(str_replace('_', ' ', $statusUpdate->new_status));
        return redirect()->route('officer.water_sentiments.show', $water_sentiment)
                        ->with('success', "Customer confirmed the status change. Water sentiment is now {$statusLabel}.");
    }

    private function processRejectedStatus(WaterSentiment $water_sentiment, StatusUpdate $statusUpdate, Request $request)
    {
        DB::transaction(function () use ($water_sentiment, $statusUpdate, $request) {
            // Update the status update record
            $statusUpdate->update([
                'status' => 'rejected',
                'customer_rejection_reason' => $request->input('rejection_reason'),
                'customer_responded_at' => now()
            ]);

            // Revert water sentiment to previous status
            $water_sentiment->update([
                'status' => $statusUpdate->old_status,
                'pending_status_update_id' => null
            ]);

            // Create notification for officer
            $this->createOfficerNotification($water_sentiment, $statusUpdate, 'rejected');
        });

        return redirect()->route('officer.water_sentiments.show', $water_sentiment)
                        ->with('warning', 'Customer rejected the status change. Please review their feedback and take appropriate action.');
    }

    private function createCustomerNotification(WaterSentiment $water_sentiment, StatusUpdate $statusUpdate)
    {
        $statusLabel = ucfirst(str_replace('_', ' ', $statusUpdate->new_status));
        
        Notification::create([
            'user_id' => $water_sentiment->user_id,
            'type' => 'status_confirmation_required',
            'title' => 'Water Sentiment Status Update Confirmation Required',
            'message' => "Your water sentiment #{$water_sentiment->id} has been marked as '{$statusLabel}' by the assigned officer. Please confirm if you agree with this status change.",
            'data' => json_encode([
                'water_sentiment_id' => $water_sentiment->id,
                'status_update_id' => $statusUpdate->id,
                'new_status' => $statusUpdate->new_status,
                'officer_notes' => $statusUpdate->officer_notes
            ]),
            'action_required' => true,
            'expires_at' => now()->addDays(7) // Give customer 7 days to respond
        ]);
    }

    private function createStatusChangeNotification(WaterSentiment $water_sentiment, $oldStatus, $newStatus)
    {
        $oldLabel = ucfirst(str_replace('_', ' ', $oldStatus));
        $newLabel = ucfirst(str_replace('_', ' ', $newStatus));
        
        Notification::create([
            'user_id' => $water_sentiment->user_id,
            'type' => 'status_changed',
            'title' => 'Water Sentiment Status Updated',
            'message' => "Your water sentiment #{$water_sentiment->id} status has been changed from '{$oldLabel}' to '{$newLabel}'.",
            'data' => json_encode([
                'water_sentiment_id' => $water_sentiment->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ])
        ]);
    }

    private function createOfficerNotification(WaterSentiment $water_sentiment, StatusUpdate $statusUpdate, $responseType)
    {
        $statusLabel = ucfirst(str_replace('_', ' ', $statusUpdate->new_status));
        
        if ($responseType === 'confirmed') {
            $message = "Customer has confirmed the status change for water sentiment #{$water_sentiment->id}. Status is now '{$statusLabel}'.";
            $title = 'Customer Confirmed Status Change';
        } else {
            $message = "Customer has rejected the status change for water sentiment #{$water_sentiment->id}. Please review their feedback.";
            $title = 'Customer Rejected Status Change';
        }

        Notification::create([
            'user_id' => Auth::id(),
            'type' => 'customer_response',
            'title' => $title,
            'message' => $message,
            'data' => json_encode([
                'water_sentiment_id' => $water_sentiment->id,
                'status_update_id' => $statusUpdate->id,
                'response_type' => $responseType
            ])
        ]);
    }

    public function getAvailableStatusOptions(WaterSentiment $water_sentiment)
    {
        $currentStatus = $water_sentiment->status;
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