<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WaterSentiment;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class BackfillWaterSentimentUserId extends Command
{
    protected $signature = 'watersentiments:backfill-user-id';
    protected $description = 'Backfill user_id in water_sentiments table based on user_email';

    public function handle()
    {
        $this->info('Starting user_id backfill for water_sentiments...');

        try {
            // Fetch sentiments with missing user_id and valid user_email
            $sentiments = WaterSentiment::where(function ($query) {
                $query->whereNull('user_id')->orWhere('user_id', 0);
            })
                ->whereNotNull('user_email')
                ->where('user_email', '!=', '')
                ->get();

            $total = $sentiments->count();
            $this->info("Found {$total} water sentiments with missing user_id.");

            if ($total === 0) {
                $this->info('No water sentiments need user_id backfill.');
                return 0;
            }

            $successCount = 0;
            $failedCount = 0;

            foreach ($sentiments as $sentiment) {
                try {
                    $user = User::where('email', $sentiment->user_email)->first();

                    if (!$user) {
                        Log::warning('No user found for backfill', [
                            'water_sentiment_id' => $sentiment->id,
                            'user_email' => $sentiment->user_email,
                        ]);
                        $failedCount++;
                        continue;
                    }

                    $sentiment->update(['user_id' => $user->id]);
                    Log::info('Updated user_id for water sentiment', [
                        'water_sentiment_id' => $sentiment->id,
                        'user_id' => $user->id,
                        'user_email' => $sentiment->user_email,
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to backfill user_id for water sentiment', [
                        'water_sentiment_id' => $sentiment->id,
                        'user_email' => $sentiment->user_email,
                        'error' => $e->getMessage(),
                    ]);
                    $failedCount++;
                }
            }

            $this->info("Backfill complete:");
            $this->info("- Successfully updated: {$successCount} water sentiments");
            $this->info("- Failed: {$failedCount} water sentiments");

            if ($failedCount > 0) {
                $this->warn('Some records failed to backfill. Check logs for details.');
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('User_id backfill process failed', [
                'error' => $e->getMessage(),
            ]);
            $this->error('Backfill process failed: ' . $e->getMessage());
            return 1;
        }
    }
}