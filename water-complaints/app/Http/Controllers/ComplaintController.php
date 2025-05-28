<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\WaterSentiment;
use App\Models\Notification;

class ComplaintController extends Controller
{
    /**
     * Display a listing of complaints.
     */
    public function index()
    {
        $complaints = WaterSentiment::all();
        return view('complaints.index', compact('complaints'));
    }

    /**
     * Show the form for creating a new complaint.
     */
    public function create()
    {
        return view('complaints.create');
    }

    /**
     * Store a newly created complaint.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_phone'   => 'required|string|max:20',
            'subcounty'    => 'required|string|max:255',
            'ward'         => 'required|string|max:255',
            'complaint'    => 'required|string',
            'frequency'    => 'required|string|max:255',
            'entity_type'  => 'required|string|max:255',
            'entity_name'  => 'nullable|string|max:255',
        ]);

        // Send the complaint to Flask API
        $response = Http::post('http://localhost:5001/analyze', [
            'complaint' => $validatedData['complaint'],
        ]);

        if ($response->successful()) {
            $data = $response->json();

            $complaint = WaterSentiment::create([
                'original_caption' => $data['original_caption'] ?? $validatedData['complaint'],
                'processed_caption' => $data['processed_caption'] ?? null,
                'sentiment' => $data['sentiment'] ?? 'Neutral',
                'category' => $data['category'] ?? 'Uncategorized',
                'source' => 'Online Form',
                'user_phone' => $validatedData['user_phone'],
                'subcounty' => $validatedData['subcounty'],
                'ward' => $validatedData['ward'],
                'complaint' => $validatedData['complaint'],
                'frequency' => $validatedData['frequency'],
                'entity_type' => $validatedData['entity_type'],
                'entity_name' => $validatedData['entity_name'],
            ]);

            return redirect()->route('complaints.index')->with('success', 'Complaint submitted successfully');
        }

        return back()->withErrors('Failed to analyze complaint. Please ensure the analysis server is running.');
    }

    /**
     * Display the specified complaint.
     */
    public function show($id)
    {
        $complaint = WaterSentiment::findOrFail($id);
        return view('complaints.show', compact('complaint'));
    }

    /**
     * Show the form for editing the specified complaint.
     */
    public function edit($id)
    {
        $complaint = WaterSentiment::findOrFail($id);
        return view('complaints.edit', compact('complaint'));
    }

    /**
     * Update the specified complaint.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'user_phone'   => 'required|string|max:20',
            'subcounty'    => 'required|string|max:255',
            'ward'         => 'required|string|max:255',
            'complaint'    => 'required|string',
            'frequency'    => 'required|string|max:255',
            'entity_type'  => 'required|string|max:255',
            'entity_name'  => 'nullable|string|max:255',
        ]);

        $complaint = WaterSentiment::findOrFail($id);
        $complaint->update($validatedData);

        return redirect()->route('complaints.index')->with('success', 'Complaint updated successfully');
    }

    /**
     * Remove the specified complaint.
     */
    public function destroy($id)
    {
        $complaint = WaterSentiment::findOrFail($id);
        $complaint->delete();

        return redirect()->route('complaints.index')->with('success', 'Complaint deleted successfully');
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

private function createOfficerNotification(Complaint $complaint, StatusUpdate $statusUpdate, $responseType)
{
    $statusLabel = ucfirst(str_replace('_', ' ', $statusUpdate->new_status));
    
    if ($responseType === 'confirmed') {
        $message = "Customer has confirmed the status change. Complaint is now '{$statusLabel}'.";
        $title = 'Customer Confirmed Status Change';
    } else {
        $message = "Customer has rejected the status change. Please review their feedback.";
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
}
