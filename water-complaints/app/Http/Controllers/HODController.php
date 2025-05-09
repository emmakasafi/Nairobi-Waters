<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WaterSentiment;
use App\Models\User;
use App\Models\Department; // Add this for accessing Department model

class HODController extends Controller
{
    /**
     * Display the HOD dashboard (listing unassigned water sentiments).
     */
    public function index()
    {
        $hod = Auth::user();
        $departmentId = $hod->department_id;

        // Get the department name
        $departmentName = Department::where('id', $departmentId)->value('name');

        // Get unassigned water sentiments
        $unassignedComplaints = WaterSentiment::where('department_id', $departmentId)
            ->whereNull('assigned_to')
            ->with('user')
            ->get();

        // Get officers with their assigned water sentiments
        $officers = User::where('role', 'officer')
            ->where('department_id', $departmentId)
            ->with(['assignedSentiments' => function ($query) {
                $query->with('user');
            }])
            ->get();

        // Check for new complaints and set a session flash message
        $newComplaintsCount = $unassignedComplaints->count();
        if ($newComplaintsCount > 0) {
            session()->flash('new_complaints', $newComplaintsCount);
        }

        // Pass the department name to the view
        return view('hod.index', compact('unassignedComplaints', 'officers', 'departmentName'));
    }

    /**
     * Assign a water sentiment to an officer.
     */
    public function assign(Request $request, $id)
    {
        $request->validate([
            'officer_id' => 'required|exists:users,id',
        ]);

        $complaint = WaterSentiment::findOrFail($id);

        $complaint->assigned_to = $request->officer_id;
        $complaint->status = 'assigned';
        $complaint->save();

        return redirect()->route('hod.index')->with('success', 'Water sentiment assigned successfully.');
    }
}
