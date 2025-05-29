<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WaterSentiment;
use App\Models\StatusUpdate;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackfillNotifications extends Command
{
    protected $signature = 'notifications:backfill';
    protected $description = 'Backfill notifications for water sentiments that require customer confirmation';

    public function handle()
    {
        $this->info('Starting notifications backfill process...');

        try {
            // Fetch water sentiments needing notifications
            $waterSentiments = WaterSentiment::where('status', 'pending_customer_confirmation')
                ->orWhereHas('statusUpdates', function ($query) {
                    $query->where('status', 'pending_confirmation');
                })
                ->with('statusUpdates')
                ->get();

            $total = $waterSentiments->count();
            $this->info("Found {$total} water sentiments to process.");

            if ($total === 0) {
                $this->info('No water sentiments require notifications.');
                return 0;
            }

            $successCount = 0;
            $skippedCount = 0;
            $failedCount = 0;

            foreach ($waterSentiments as $waterSentiment) {
                try {
                    // Validate user_id
                    if (!$waterSentiment->user_id) {
                        Log::warning('Skipping water sentiment due to missing user_id', [
                            'water_sentiment_id' => $waterSentiment->id,
                            'user_email' => $waterSentiment->user_email,
                        ]);
                        $failedCount++;
                        continue;
                    }

                    $user = User::find($waterSentiment->user_id);
                    if (!$user) {
                        Log::warning('Skipping water sentiment due to invalid user', [
                            'water_sentiment_id' => $waterSentiment->id,
                            'user_id' => $waterSentiment->user_id,
                        ]);
                        $failedCount++;
                        continue;
                    }

                    // Find the latest pending_confirmation status update
                    $statusUpdate = $waterSentiment->statusUpdates
                        ->where('status', 'pending_confirmation')
                        ->sortByDesc('created_at')
                        ->first();

                    if (!$statusUpdate) {
                        Log::warning('No pending_confirmation status update found', [
                            'water_sentiment_id' => $waterSentiment->id,
                            'user_id' => $waterSentiment->user_id,
                        ]);
                        $failedCount++;
                        continue;
                    }

                    // Check for existing notification
                    $existingNotification = Notification::where('user_id', $waterSentiment->user_id)
                        ->where('type', 'status_confirmation_required')
                        ->whereJsonContains('data->water_sentiment_id', $waterSentiment->id)
                        ->whereJsonContains('data->status_update_id', $statusUpdate->id)
                        ->where('action_required', true)
                        ->where('expires_at', '>', now())
                        ->exists();

                    if ($existingNotification) {
                        Log::info('Notification already exists, skipping', [
                            'water_sentiment_id' => $waterSentiment->id,
                            'user_id' => $waterSentiment->user_id,
                            'status_update_id' => $statusUpdate->id,
                        ]);
                        $skippedCount++;
                        continue;
                    }

                    // Create notification
                    $notificationData = [
                        'user_id' => $waterSentiment->user_id,
                        'type' => 'status_confirmation_required',
                        'title' => 'Complaint Status Update',
                        'message' => "Your complaint #{$waterSentiment->id} has been marked as " . ucfirst($statusUpdate->new_status) . ". Please confirm or reject this status.",
                        'data' => json_encode([
                            'water_sentiment_id' => $waterSentiment->id,
                            'status' => $statusUpdate->new_status,
                            'status_update_id' => $statusUpdate->id,
                        ]),
                        'action_required' => true,
                        'expires_at' => now()->addDays(7),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    DB::transaction(function () use ($notificationData, $waterSentiment, $statusUpdate) {
                        Notification::create($notificationData);
                        // Ensure water_sentiment has pending_status_update_id
                        $waterSentiment->update([
                            'pending_status_update_id' => $statusUpdate->id,
                        ]);
                    });

                    Log::info('Notification backfilled successfully', [
                        'notification_data' => $notificationData,
                        'water_sentiment_id' => $waterSentiment->id,
                        'user_id' => $waterSentiment->user_id,
                        'status_update_id' => $statusUpdate->id,
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to backfill notification', [
                        'water_sentiment_id' => $waterSentiment->id,
                        'user_id' => $waterSentiment->user_id ?? null,
                        'status_update_id' => $statusUpdate->id ?? null,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $failedCount++;
                }
            }

            $this->info("Backfill complete:");
            $this->info("- Successfully created: {$successCount} notifications");
            $this->info("- Skipped (already exist): {$skippedCount} notifications");
            $this->info("- Failed: {$failedCount} notifications");

            if ($failedCount > 0) {
                $this->warn('Some notifications failed to backfill. Check logs for details.');
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Backfill process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Backfill process failed: ' . $e->getMessage());
            return 1;
        }
    }
}