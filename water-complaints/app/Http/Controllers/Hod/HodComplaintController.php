<?php
namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Complaint;
use App\Models\User;

class HodComplaintController extends Controller
{
    public function index()
    {
        $unassignedComplaints = Complaint::whereNull('assigned_to')->get();
        $officers = User::where('role', 'officer')->get();

        return view('hod.index', compact('unassignedComplaints', 'officers'));
    }

    public function assign(Request $request, Complaint $complaint)
    {
        $request->validate([
            'officer_id' => 'required|exists:users,id',
        ]);

        $complaint->assigned_to = $request->officer_id;
        $complaint->save();

        return redirect()->route('hod.index')->with('success', 'Complaint assigned successfully.');
    }
}

