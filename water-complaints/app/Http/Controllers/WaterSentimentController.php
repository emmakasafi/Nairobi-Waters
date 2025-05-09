<?php

namespace App\Http\Controllers;

use App\Models\WaterSentiment;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use DataTables;

class WaterSentimentController extends Controller
{
    /**
     * Display a listing of the water sentiments.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $water_sentiments = WaterSentiment::all();
        return view('admin.water_sentiments', compact('water_sentiments'));
    }

    /**
     * Show the form for creating a new water sentiment.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.water_sentiments.create');
    }

    /**
     * Store a newly created water sentiment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'original_caption' => 'required|string|max:255',
            'processed_caption' => 'required|string|max:255',
            'timestamp' => 'required|date',
            'overall_sentiment' => 'required|string|max:255',
            'complaint_category' => 'required|string|max:255',
            'source' => 'required|string|max:255',
            'county' => 'required|string|max:255',
            'subcounty' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
        ]);

        $waterSentiment = WaterSentiment::create($validatedData);

        return redirect()->route('water_sentiments.index')
                         ->with('success', 'Water Sentiment created successfully.');
    }

    /**
     * Display the specified water sentiment.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $waterSentiment = WaterSentiment::findOrFail($id);
        $departments = Department::all();
        $officers = User::where('role', 'officer')->get(); // Adjust 'officer' if you use other role names

        return view('admin.water_sentiments.show', compact('waterSentiment', 'departments', 'officers'));
    }

    /**
     * Show the form for editing the specified water sentiment.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $waterSentiment = WaterSentiment::findOrFail($id);
        return view('admin.water_sentiments.edit', compact('waterSentiment'));
    }

    /**
     * Update the specified water sentiment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $waterSentiment = WaterSentiment::findOrFail($id);
        $validatedData = $request->validate([
            'original_caption' => 'required|string|max:255',
            'processed_caption' => 'required|string|max:255',
            'timestamp' => 'required|date',
            'overall_sentiment' => 'required|string|max:255',
            'complaint_category' => 'required|string|max:255',
            'source' => 'required|string|max:255',
            'county' => 'required|string|max:255',
            'subcounty' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
        ]);

        $waterSentiment->update($validatedData);

        return redirect()->route('water_sentiments.index')
                         ->with('success', 'Water Sentiment updated successfully');
    }

    /**
     * Remove the specified water sentiment from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        WaterSentiment::destroy($id);
        return redirect()->route('water_sentiments.index')
                         ->with('success', 'Water Sentiment deleted successfully');
    }

    /**
     * Search for water sentiments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $results = WaterSentiment::where('original_caption', 'like', "%{$query}%")
            ->orWhere('processed_caption', 'like', "%{$query}%")
            ->get();
        return response()->json($results);
    }

    /**
     * Get data for the DataTables.
     *
     * @return \Yajra\DataTables\DataTables
     */
    public function dataTable()
    {
        $query = WaterSentiment::query();

        return DataTables::of($query)
            ->addColumn('actions', function ($water_sentiment) {
                return '
                    <a href="' . route('water_sentiments.show', $water_sentiment->id) . '" class="px-4 py-2 bg-green-500 hover:bg-green-700 text-white rounded-lg">View</a>
                    <a href="' . route('water_sentiments.edit', $water_sentiment->id) . '" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-700 text-white rounded-lg">Edit</a>
                    <form action="' . route('water_sentiments.destroy', $water_sentiment->id) . '" method="POST" class="inline-block">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-700 text-white rounded-lg">Delete</button>
                    </form>
                ';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Assign a water sentiment to a department or officer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assign(Request $request, $id)
    {
        $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'assigned_to'   => 'nullable|exists:users,id',
            'admin_notes'   => 'nullable|string',
        ]);

        $sentiment = WaterSentiment::findOrFail($id);
        $sentiment->department_id = $request->department_id;
        $sentiment->assigned_to = $request->assigned_to;
        $sentiment->assigned_by = auth()->id(); // make sure user is logged in
        $sentiment->admin_notes = $request->admin_notes;
        $sentiment->assigned_at = now();
        $sentiment->status = 'Assigned'; // optional

        $sentiment->save();

        return redirect()->back()->with('success', 'Complaint assigned successfully.');
    }
}
