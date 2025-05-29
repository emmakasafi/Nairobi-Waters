<?php
namespace App\Http\Controllers;

use App\Models\WaterSentiment;
use App\Models\Notification;
use App\Models\StatusUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Fetch water sentiments by user_id or user_email
        $waterSentiments = WaterSentiment::where('user_id', $user->id)
            ->orWhere('user_email', $user->email)
            ->with('user', 'assignedOfficer')
            ->orderBy('timestamp', 'desc')
            ->get()
            ->map(function ($sentiment) use ($user) {
                $sentiment->awaiting_confirmation = Notification::where('user_id', $user->id)
                    ->where('type', 'status_confirmation_required')
                    ->where('action_required', true)
                    ->where('expires_at', '>', now())
                    ->whereJsonContains('data->water_sentiment_id', $sentiment->id)
                    ->exists();
                $sentiment->pending_status = $sentiment->awaiting_confirmation
                    ? StatusUpdate::where('water_sentiment_id', $sentiment->id)
                        ->where('status', 'pending_confirmation')
                        ->latest()
                        ->value('new_status')
                    : null;
                return $sentiment;
            });

        // Calculate complaint stats
        $totalComplaints = $waterSentiments->count();
        $resolvedComplaints = $waterSentiments->where('status', 'resolved')->count();
        $pendingComplaints = $waterSentiments->where('status', 'pending')->count();
        $assignedComplaints = $waterSentiments->where('status', 'assigned')->count();

        // Fetch pending confirmations count
        $pendingConfirmations = Notification::where('user_id', $user->id)
            ->where('type', 'status_confirmation_required')
            ->where('action_required', true)
            ->where('expires_at', '>', now())
            ->count();

        // Log dashboard data for debugging
        Log::info('Customer dashboard loaded', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'total_complaints' => $totalComplaints,
            'pending_confirmations' => $pendingConfirmations,
            'water_sentiment_ids' => $waterSentiments->pluck('id')->toArray(),
        ]);

        return view('customer-dashboard', compact(
            'waterSentiments',
            'resolvedComplaints',
            'pendingComplaints',
            'assignedComplaints',
            'totalComplaints',
            'pendingConfirmations'
        ));
    }
}