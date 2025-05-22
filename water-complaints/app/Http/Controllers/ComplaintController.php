<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\WaterSentiment;
use App\Models\Officer; // Ensure you have this model

class ComplaintController extends Controller
{
    /**
     * Display a listing of complaints.
     */
    public function index(Request $request)
    {
        $query = $request->input('query');
        $status = $request->input('status');

        // Start query builder
        $complaintsQuery = WaterSentiment::query();

        // Search by original complaint content
        if ($query) {
            $complaintsQuery->where('original_caption', 'like', '%' . $query . '%');
        }

        // Filter by status if provided
        if ($status) {
            $complaintsQuery->where('status', $status);
        }

        // Get the filtered results
        $complaints = $complaintsQuery->orderBy('timestamp', 'desc')->get();

        // Metrics for dashboard summary
        $totalComplaints = WaterSentiment::count();
        $resolvedComplaints = WaterSentiment::where('status', 'resolved')->count();
        $pendingComplaints = WaterSentiment::where('status', 'pending')->count();
        $assignedComplaints = WaterSentiment::where('status', 'assigned')->count();

        // Fetch officers and timestamps for filters
        $officers = Officer::all(); // Fetch all officers
        $timestamps = WaterSentiment::select('timestamp')->distinct()->get()->pluck('timestamp'); // Fetch distinct timestamps

        return view('complaints.index', compact(
            'complaints',
            'totalComplaints',
            'resolvedComplaints',
            'pendingComplaints',
            'assignedComplaints',
            'officers',
            'timestamps'
        ));
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
}