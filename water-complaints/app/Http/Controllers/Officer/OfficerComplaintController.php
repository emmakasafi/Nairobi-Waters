<?php
namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Complaint;
use Illuminate\Support\Facades\Auth;

class OfficerComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::where('assigned_to', Auth::id())->get();
        return view('officer.index', compact('complaints'));
    }

    public function show(Complaint $complaint)
    {
        if ($complaint->assigned_to !== Auth::id()) {
            abort(403);
        }

        return view('officer.show', compact('complaint'));
    }

    public function update(Request $request, Complaint $complaint)
    {
        if ($complaint->assigned_to !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:in_progress,resolved',
            'resolution_notes' => 'nullable|string',
        ]);

        $complaint->status = $request->status;
        $complaint->resolution_notes = $request->resolution_notes;
        $complaint->save();

        return redirect()->route('officer.dashboard')->with('success', 'Complaint updated successfully.');
    }
}
