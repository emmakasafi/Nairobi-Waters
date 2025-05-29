<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\StatusUpdate;
use App\Models\WaterSentiment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackfillOfficerNotifications extends Command
{
    protected $signature = 'notifications:backfill-officer {--user_id= : Specific officer user ID}';
    protected $description = 'Backfill customer_response notifications for officers based on past customer responses';

    public function handle()
    {
        $this->info('Starting backfill of officer notifications...');

        $query = StatusUpdate::whereIn('status', ['confirmed', 'rejected'])
            ->whereNotNull('customer_responded_at');

        if ($userId = $this->option('user_id')) {
            $this->info("Backfilling for officer user_id: $userId");
            $query->whereHas('waterSentiment', function ($q) use ($userId) {
                $q->where('assigned_to', $userId);
            });
        }

        $statusUpdates = $query->get();

        if ($statusUpdates->isEmpty()) {
            $this->error('No customer responses found in status_updates.');
            return;
        }

        $created = 0;
        $skipped = 0;

        foreach ($statusUpdates as $statusUpdate) {
            try {
                $complaint = $statusUpdate->waterSentiment;

                if (!$complaint) {
                    Log::warning('Skipping status update: complaint not found', [
                        'status_update_id' => $statusUpdate->id,
                        'water_sentiment_id' => $statusUpdate->water_sentiment_id,
                    ]);
                    $skipped++;
                    continue;
                }

                if (!$complaint->assigned_to) {
                    Log::warning('Skipping status update: no officer assigned', [
                        'status_update_id' => $statusUpdate->id,
                        'water_sentiment_id' => $statusUpdate->water_sentiment_id,
                    ]);
                    $skipped++;
                    continue;
                }

                $existing = Notification::where('user_id', $complaint->assigned_to)
                    ->where('type', 'customer_response')
                    ->where('data->water_sentiment_id', $complaint->id)
                    ->where('data->response', $statusUpdate->status)
                    ->exists();

                if ($existing) {
                    Log::info('Notification already exists', [
                        'officer_id' => $complaint->assigned_to,
                        'water_sentiment_id' => $complaint->id,
                        'response' => $statusUpdate->status,
                    ]);
                    $skipped++;
                    continue;
                }

                $officerMessage = $statusUpdate->status === 'confirmed'
                    ? "Customer confirmed the {$statusUpdate->new_status} status for complaint #{$complaint->id}."
                    : "Customer rejected the {$statusUpdate->new_status} status for complaint #{$complaint->id}. Reason: " . ($statusUpdate->customer_rejection_reason ?? 'No reason provided');

                Notification::create([
                    'user_id' => $complaint->assigned_to,
                    'type' => 'customer_response',
                    'title' => 'Customer Response for Complaint #' . $complaint->id,
                    'message' => $officerMessage,
                    'data' => [
                        'water_sentiment_id' => $complaint->id,
                        'status_update_id' => $statusUpdate->id,
                        'response' => $statusUpdate->status,
                        'rejection_reason' => $statusUpdate->customer_rejection_reason ?? null,
                    ],
                    'action_required' => $statusUpdate->status === 'rejected',
                    'expires_at' => now()->addDays(7),
                    'created_at' => $statusUpdate->customer_responded_at,
                    'updated_at' => $statusUpdate->customer_responded_at,
                ]);

                Log::info('Created notification for officer', [
                    'officer_id' => $complaint->assigned_to,
                    'water_sentiment_id' => $complaint->id,
                    'response' => $statusUpdate->status,
                ]);
                $created++;
            } catch (\Exception $e) {
                Log::error('Failed to create notification', [
                    'status_update_id' => $statusUpdate->id,
                    'error' => $e->getMessage(),
                ]);
                $skipped++;
            }
        }

        $this->info("Backfill completed: $created notifications created, $skipped skipped.");
    }
}