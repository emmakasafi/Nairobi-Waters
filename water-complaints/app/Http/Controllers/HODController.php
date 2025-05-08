<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WaterSentiment;
use App\Models\User;

class HODController extends Controller
{
    /**
     * Display the HOD dashboard (listing unassigned water sentiments).
     */
    public function index()
{
    $hod = Auth::user();
    $departmentId = $hod->department_id;

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

    return view('hod.index', compact('unassignedComplaints', 'officers'));
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
