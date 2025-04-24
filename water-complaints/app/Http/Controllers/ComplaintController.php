<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\WaterSentiment;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the complaints.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $complaints = WaterSentiment::all();
        return view('complaints.index', compact('complaints'));
    }

    /**
     * Show the form for creating a new complaint.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('complaints.create');
    }

    /**
     * Store a newly created complaint in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'user_email' => 'required|email',
            'user_phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'subcounty' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'complaint' => 'required|string',
            'date_time' => 'required|date',
            'frequency' => 'required|string|max:255',
            'severity' => 'required|string|max:255',
            'entity_type' => 'required|string|max:255',
            'entity_name' => 'nullable|string|max:255',
        ]);

        // Send data to Flask API
        $response = Http::post('http://localhost:5001/analyze', [
            'complaint' => $validatedData['complaint'],
            'user_email' => $validatedData['user_email'],
            'user_phone' => $validatedData['user_phone'],
        ]);

        if ($response->successful()) {
            // Store the response in the database
            $data = $response->json();
            $complaint = new WaterSentiment([
                'original_caption' => $data['original_caption'],
                'processed_caption' => $data['processed_caption'],
                'sentiment' => $data['sentiment'],
                'category' => $data['category'],
                'source' => 'Online Form',
                'user_email' => $validatedData['user_email'],
                'user_phone' => $validatedData['user_phone'],
                'subcounty' => $validatedData['subcounty'],
                'ward' => $validatedData['ward'],
                'complaint' => $validatedData['complaint'],
                'date_time' => $validatedData['date_time'],
                'frequency' => $validatedData['frequency'],
                'severity' => $validatedData['severity'],
                'entity_type' => $validatedData['entity_type'],
                'entity_name' => $validatedData['entity_name'],
            ]);
            $complaint->save();

            return redirect()->route('complaints.index')->with('success', 'Complaint submitted successfully');
        } else {
            return back()->withErrors('Failed to submit complaint');
        }
    }

    /**
     * Display the specified complaint.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $complaint = WaterSentiment::findOrFail($id);
        return view('complaints.show', compact('complaint'));
    }

    /**
     * Show the form for editing the specified complaint.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $complaint = WaterSentiment::findOrFail($id);
        return view('complaints.edit', compact('complaint'));
    }

    /**
     * Update the specified complaint in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'user_email' => 'required|email',
            'user_phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'subcounty' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'complaint' => 'required|string',
            'date_time' => 'required|date',
            'frequency' => 'required|string|max:255',
            'severity' => 'required|string|max:255',
            'entity_type' => 'required|string|max:255',
            'entity_name' => 'nullable|string|max:255',
        ]);

        $complaint = WaterSentiment::findOrFail($id);
        $complaint->update($validatedData);

        return redirect()->route('complaints.index')->with('success', 'Complaint updated successfully');
    }

    /**
     * Remove the specified complaint from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $complaint = WaterSentiment::findOrFail($id);
        $complaint->delete();

        return redirect()->route('complaints.index')->with('success', 'Complaint deleted successfully');
    }
}