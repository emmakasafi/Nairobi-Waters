<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Complaint;
use App\Models\User;

class HODController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hod = Auth::user();
        $departmentId = $hod->department_id;

        // Get complaints that belong to this department and are not assigned
        $unassignedComplaints = Complaint::where('department_id', $departmentId)
            ->whereNull('assigned_to')
            ->get();

        // Get officers in the same department
        $officers = User::where('role', 'officer')
            ->where('department_id', $departmentId)
            ->get();

        return view('hod.index', compact('unassignedComplaints', 'officers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function assign(Request $request, $complaintId)
    {
        $request->validate([
            'officer_id' => 'required|exists:users,id',
        ]);

        $complaint = Complaint::findOrFail($complaintId);
        $complaint->assigned_to = $request->officer_id;
        $complaint->status = 'assigned';
        $complaint->save();

        // Redirect to 'hod.index' instead of 'hod.dashboard'
        return redirect()->route('hod.index')->with('success', 'Complaint assigned successfully.');
    }
}
