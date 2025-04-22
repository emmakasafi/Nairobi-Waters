<?php

namespace App\Http\Controllers;

use App\Models\WaterSentiment;
use App\Models\User;
use Illuminate\Http\Request;

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

        $recentComplaints = WaterSentiment::where('timestamp', '!=', null)
        ->orderBy('timestamp', 'desc')
        ->take(3)
        ->get();

        $totalUsers = User::count();
        $newUsersToday = User::whereDate('created_at', today())->count();

        return view('admin-dashboard', compact('recentComplaints', 'totalUsers', 'newUsersToday'));
    }
}