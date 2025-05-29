<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\WaterSentiment;
use App\Models\Notification;
use App\Models\NairobiLocation;

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
        $subcounties = NairobiLocation::distinct()->pluck('subcounty')->toArray();
        return view('complaints.create', compact('subcounties'));
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

    /**
     * Get wards for a given subcounty.
     */
    public function getWards($subcounty)
    {
        $wards = NairobiLocation::where('subcounty', $subcounty)->pluck('ward')->toArray();
        return response()->json($wards);
    }
}