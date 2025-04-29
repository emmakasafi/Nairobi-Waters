<?php

namespace App\Http\Controllers;

use App\Models\WaterSentiment;
use App\Models\User;
use App\Models\Department; // Add this line
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = WaterSentiment::orderBy('timestamp', 'desc');

        // Apply filters if they exist in the request
        if ($request->has('category')) {
            $query->where('complaint_category', $request->category);
        }
        if ($request->has('subcounty')) {
            $query->where('subcounty', $request->subcounty);
        }
        if ($request->has('ward')) {
            $query->where('ward', $request->ward);
        }
        if ($request->has('sentiment')) {
            $query->where('overall_sentiment', $request->sentiment);
        }
        if ($request->has('source')) {
            $query->where('source', $request->source);
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('timestamp', [$request->start_date, $request->end_date]);
        }

        // Fetch recent complaints
        $recentComplaints = $query->paginate(10);

        // Total number of complaints
        $totalComplaints = WaterSentiment::count();

        // Other data
        $totalUsers = User::count();
        $newUsersToday = User::whereDate('created_at', today())->count();

        // Data for sentiment chart
        $sentimentData = WaterSentiment::select('overall_sentiment', DB::raw('COUNT(*) as count'))
            ->groupBy('overall_sentiment')
            ->get();

        // Data for sentiment trend chart
        $sentimentTrendData = WaterSentiment::select(DB::raw('DATE(timestamp) as date'), DB::raw('COUNT(*) as count'), 'overall_sentiment')
            ->groupBy('date', 'overall_sentiment')
            ->orderBy('date')
            ->get();

        // Data for complaints per subcounty
        $complaintsPerSubcounty = WaterSentiment::select('subcounty', DB::raw('COUNT(*) as count'))
            ->groupBy('subcounty')
            ->get();

        // Data for complaints per ward
        $complaintsPerWard = WaterSentiment::select('ward', DB::raw('COUNT(*) as count'))
            ->groupBy('ward')
            ->get();

        // Data for complaints per category
        $complaintsPerCategory = WaterSentiment::select('complaint_category', DB::raw('COUNT(*) as count'))
            ->groupBy('complaint_category')
            ->get();

        // Fetch unique values for filters
        $categories = WaterSentiment::select('complaint_category')->distinct()->get()->pluck('complaint_category');
        $subcounties = WaterSentiment::select('subcounty')->distinct()->get()->pluck('subcounty');
        $wards = WaterSentiment::select('ward')->distinct()->get()->pluck('ward');
        $sentiments = WaterSentiment::select('overall_sentiment')->distinct()->get()->pluck('overall_sentiment');
        $sources = WaterSentiment::select('source')->distinct()->get()->pluck('source');

        // Fetch departments
        $departments = Department::all();

        return view('admin-dashboard', compact(
            'recentComplaints',
            'totalComplaints',
            'totalUsers',
            'newUsersToday',
            'sentimentData',
            'sentimentTrendData',
            'complaintsPerSubcounty',
            'complaintsPerWard',
            'complaintsPerCategory',
            'categories',
            'subcounties',
            'wards',
            'sentiments',
            'sources',
            'departments' // Add this line
        ));
    }
public function dashboard()
{
    $departments = Department::all();
    // Other data fetching logic...

    return view('admin.dashboard', compact('departments'));
}
}