<?php
namespace App\Http\Controllers;

use App\Models\WaterSentiment;
use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{
public function index()
{
    // Get all water sentiments (complaints) for the authenticated user along with related user and assigned officer
    $waterSentiments = WaterSentiment::where('user_id', auth()->id())
        ->with('user', 'assigned_to') // Fetch the user and assigned officer relationships
        ->latest() // Get the most recent sentiments first
        ->get(); // Retrieve all water sentiments for the user

    // Get count of total, resolved, and pending complaints
    $resolvedComplaints = $waterSentiments->where('status', 'resolved')->count();
    $pendingComplaints = $waterSentiments->where('status', 'pending')->count();
    $totalComplaints = $waterSentiments->count(); // Count total complaints

    // Debug: Check if the variables are set
    \Log::info('Total Complaints: ' . $totalComplaints);
    \Log::info('Resolved Complaints: ' . $resolvedComplaints);
    \Log::info('Pending Complaints: ' . $pendingComplaints);

    // Pass the data to the view
    return view('customer-dashboard', compact('waterSentiments', 'resolvedComplaints', 'pendingComplaints', 'totalComplaints'));
}
}