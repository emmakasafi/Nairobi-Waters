<?php
namespace App\Http\Controllers;

use App\Models\WaterSentiment;
use App\Models\Notification;
use App\Models\StatusUpdate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Fetch water sentiments
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

        // Calculate stats
        $totalComplaints = $waterSentiments->count();
        $resolvedComplaints = $waterSentiments->where('status', 'resolved')->count();
        $pendingComplaints = $waterSentiments->where('status', 'pending')->count();
        $assignedComplaints = $waterSentiments->where('status', 'in_progress')->count();

        // Fetch pending confirmations
        $pendingConfirmations = Notification::where('user_id', $user->id)
            ->where('type', 'status_confirmation_required')
            ->where('action_required', true)
            ->where('expires_at', '>', now())
            ->count();

        Log::info('Customer dashboard loaded', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'total_complaints' => $totalComplaints,
            'resolved_complaints' => $resolvedComplaints,
            'pending_complaints' => $pendingComplaints,
            'assigned_complaints' => $assignedComplaints,
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

    public function show($id)
    {
        $user = Auth::user();
        $waterSentiment = WaterSentiment::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('user_email', $user->email);
            })
            ->with('user', 'assignedOfficer', 'statusUpdates')
            ->firstOrFail();

        Log::info('Customer viewed complaint details', [
            'user_id' => $user->id,
            'water_sentiment_id' => $waterSentiment->id,
            'status' => $waterSentiment->status,
        ]);

        return view('customer.complaints.show', compact('waterSentiment'));
    }
}