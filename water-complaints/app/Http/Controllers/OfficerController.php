<?php

namespace App\Http\Controllers;
use App\Models\WaterSentiment; 
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfficerController extends Controller
{
    /**
     * Display a listing of the resource with enhanced dashboard data.
     */
    public function index(Request $request)
    {
        // Start building the query
        $query = WaterSentiment::with(['user', 'department'])
                              ->where('assigned_to', auth()->id());

        // Apply filters if they exist
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sentiment')) {
            $query->where('overall_sentiment', $request->sentiment);
        }

        if ($request->filled('category')) {
            $query->where('complaint_category', $request->category);
        }

        if ($request->filled('subcounty')) {
            $query->where('subcounty', $request->subcounty);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('timestamp', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('timestamp', '<=', $request->date_to);
        }

        // Get paginated results
        $waterSentiments = $query->orderBy('timestamp', 'desc')->paginate(10);

        // Get dashboard statistics
        $stats = $this->getDashboardStats();

        // Pass data to the view
        return view('officer.index', compact('waterSentiments', 'stats'));
    }

    /**
     * Get dashboard statistics for the officer.
     */
    private function getDashboardStats()
    {
        $officerId = auth()->id();
        
        $totalComplaints = WaterSentiment::where('assigned_to', $officerId)->count();
        
        $pendingComplaints = WaterSentiment::where('assigned_to', $officerId)
                                          ->where('status', 'pending')
                                          ->count();
        
        $resolvedComplaints = WaterSentiment::where('assigned_to', $officerId)
                                           ->where('status', 'resolved')
                                           ->count();
        
        $inProgressComplaints = WaterSentiment::where('assigned_to', $officerId)
                                             ->where('status', 'in_progress')
                                             ->count();

        // Get sentiment distribution
        $sentimentStats = WaterSentiment::where('assigned_to', $officerId)
                                       ->select('overall_sentiment', DB::raw('count(*) as count'))
                                       ->groupBy('overall_sentiment')
                                       ->pluck('count', 'overall_sentiment')
                                       ->toArray();

        // Get category distribution
        $categoryStats = WaterSentiment::where('assigned_to', $officerId)
                                      ->select('complaint_category', DB::raw('count(*) as count'))
                                      ->groupBy('complaint_category')
                                      ->pluck('count', 'complaint_category')
                                      ->toArray();

        // Recent activity (last 7 days)
        $recentComplaints = WaterSentiment::where('assigned_to', $officerId)
                                         ->where('timestamp', '>=', now()->subDays(7))
                                         ->count();

        return [
            'total' => $totalComplaints,
            'pending' => $pendingComplaints,
            'resolved' => $resolvedComplaints,
            'in_progress' => $inProgressComplaints,
            'recent' => $recentComplaints,
            'sentiment' => $sentimentStats,
            'categories' => $categoryStats,
        ];
    }

    /**
     * Display the specified resource with full details.
     */
    public function show(string $id)
    {
        $waterSentiment = WaterSentiment::with(['user', 'assignedOfficer', 'department'])
                                       ->where('assigned_to', auth()->id())
                                       ->findOrFail($id);
        
        return view('officer.show', compact('waterSentiment'));
    }

    /**
     * Update complaint status.
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,closed',
            'notes' => 'nullable|string|max:1000'
        ]);

        $waterSentiment = WaterSentiment::where('assigned_to', auth()->id())
                                       ->findOrFail($id);
        
        $waterSentiment->update([
            'status' => $request->status,
            'officer_notes' => $request->notes,
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Complaint status updated successfully!');
    }
}