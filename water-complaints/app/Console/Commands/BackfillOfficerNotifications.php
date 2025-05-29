<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\WaterSentiment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class BackfillOfficerNotifications extends Command
{
    protected $signature = 'notifications:backfill-officer {--user_id= : Specific officer user ID}';
    protected $description = 'Backfill customer_response notifications for officers based on past customer responses';

    public function handle()
    {
        $this->info('Starting backfill of officer notifications...');

        $query = Notification::where('type', 'response_acknowledgement')->whereNotNull('data');

        if ($userId = $this->option('user_id')) {
            $this->info("Backfilling for officer user_id: $userId");
            $complaintIds = WaterSentiment::where('assigned_to', $userId)->pluck('id')->toArray();
            $query->where(function ($q) use ($complaintIds) {
                foreach ($complaintIds as $id) {
                    $q->orWhereJsonContains('data->water_sentiment_id', $id);
                }
            });
        }

        $responses = $query->get();

        if ($responses->isEmpty()) {
            $this->error('No customer response_acknowledgement notifications found.');
            return;
        }

        $created = 0;
        $skipped = 0;

        foreach ($responses as $response) {
            try {
                $data = is_array($response->data) ? $response->data : json_decode($response->data, true);

                if (!isset($data['water_sentiment_id']) || !isset($data['response'])) {
                    $this->error('Invalid response data');
                    Log::warning('Skipping response: missing required fields', [
                        'response_id' => $response->id,
                        'data' => $response->data
                    ]);
                    $skipped++;
                    continue;
                }

                $complaint = WaterSentiment::find($data['water_sentiment_id']);

                if (!$complaint) {
                    Log::warning('Skipping response: complaint not found', [
                        'notification_id' => $response->id,
                        'water_sentiment_id' => $data['water_sentiment_id']
                    ]);
                    $skipped++;
                    continue;
                }

                if (!$complaint->assigned_to) {
                    Log::warning('Skipping response: no officer assigned', [
                        'notification_id' => $response->id,
                        'water_sentiment_id' => $data['water_sentiment_id']
                    ]);
                    $skipped++;
                    continue;
                }

                // Check for duplicate notification
                $existing = Notification::where('user_id', $complaint->assigned_to)
                    ->where('type', 'customer_response')
                    ->where('data->water_sentiment_id', $data['water_sentiment_id'])
                    ->where('data->response', $data['response'])
                    ->exists();

                if ($existing) {
                    Log::info('Notification already exists', [
                        'officer_id' => $complaint->assigned_to,
                        'water_sentiment_id' => $data['water_sentiment_id'],
                        'response' => $data['response']
                    ]);
                    $skipped++;
                    continue;
                }

                $officerMessage = $data['response'] === 'confirmed'
                    ? "Customer confirmed the " . (isset($data['status']) ? $data['status'] : 'unknown') . " status for complaint #{$complaint->id}."
                    : "Customer rejected the " . (isset($data['status']) ? $data['status'] : 'unknown') . " status for complaint #{$complaint->id}. Reason: " . (isset($data['rejection_reason']) ? $data['rejection_reason'] : 'No reason provided');

                Notification::create([
                    'user_id' => $complaint->assigned_to,
                    'type' => 'customer_response',
                    'title' => 'Customer Response for Complaint #' . $complaint->id,
                    'message' => $officerMessage,
                    'data' => [
                        'water_sentiment_id' => $data['water_sentiment_id'],
                        'customer_notification_id' => $response->id,
                        'response' => $data['response'],
                        'rejection_reason' => isset($data['rejection_reason']) ? $data['rejection_reason'] : null,
                    ],
                    'action_required' => $data['response'] === 'rejected',
                    'expires_at' => now()->addDays(7),
                    'created_at' => $response->created_at,
                    'updated_at' => $response->created_at,
                ]);

                Log::info('Created notification for officer', [
                    'officer_id' => $complaint->assigned_to,
                    'water_sentiment_id' => $data['water_sentiment_id'],
                    'response' => $data['response']
                ]);
                $created++;
            } catch (\Exception $e) {
                Log::error('Failed to create notification', [
                    'response_id' => $response->id,
                    'error' => $e->getMessage(),
                    'data' => $data
                ]);
                $skipped++;
            }
        }

        $this->info("Backfill completed: $created notifications created, $skipped skipped.");
    }
}