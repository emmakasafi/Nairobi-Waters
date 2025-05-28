<?php

namespace App\Http\Controllers;

use App\Models\WaterSentiment;
use App\Models\Notification;
use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        // Fetch the email of the authenticated user
        $userEmail = auth()->user()->email;

        // Fetch water sentiments for the authenticated user, ordered by the 'timestamp' column
        $waterSentiments = WaterSentiment::where('user_email', $userEmail)
            ->with('user', 'assignedOfficer') // Eager load the user and assigned officer
            ->orderBy('timestamp', 'desc') // Order by the 'timestamp' column in descending order
            ->get();

        // Calculate the counts for resolved and pending complaints
        $resolvedComplaints = $waterSentiments->where('status', 'resolved')->count();
        $pendingComplaints = $waterSentiments->where('status', 'pending')->count();
        $assignedComplaints = $waterSentiments->where('status', 'assigned')->count(); // Count assigned complaints
        $totalComplaints = $waterSentiments->count();

        // Fetch notifications for the authenticated user
        $notifications = Notification::where('user_id', auth()->id())
            ->where('type', 'status_confirmation_required')
            ->where('action_required', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // Fetch pending confirmations
        $pendingConfirmations = $notifications->where('action_required', true);

        // Pass the data to the view
        return view('customer-dashboard', compact(
            'waterSentiments',
            'resolvedComplaints',
            'pendingComplaints',
            'assignedComplaints',
            'totalComplaints',
            'notifications',
            'pendingConfirmations'
        ));
    }
}