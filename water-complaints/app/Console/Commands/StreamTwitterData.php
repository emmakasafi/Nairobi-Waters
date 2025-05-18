<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TwitterData;
use Symfony\Component\Process\Process;

class StreamTwitterData extends Command
{
    protected $signature = 'twitter:stream';
    protected $description = 'Stream X posts about water complaints and store in twitter_data';

    public function handle()
    {
        $this->info('Starting Twitter stream...');

        // Run Python streaming script
        $process = new Process(['python', base_path('scripts/stream_tweets.py')]);
        $process->setTimeout(null);
        $process->start();

        // Process output line by line
        $process->wait(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->error("Error: $buffer");
                return;
            }

            // Parse JSON output
            try {
                $tweet_data = json_decode($buffer, true);
                if (!$tweet_data || !isset($tweet_data['tweet_id'])) {
                    return;
                }

                // Store in twitter_data
                TwitterData::updateOrCreate(
                    ['tweet_id' => $tweet_data['tweet_id']],
                    [
                        'text' => $tweet_data['text'],
                        'created_at' => $tweet_data['created_at'],
                        'user_handle' => $tweet_data['user_handle'],
                        'sentiment_score' => $tweet_data['sentiment_score'],
                        'sentiment_label' => $tweet_data['sentiment_label'],
                        'keywords' => $tweet_data['keywords'],
                        'language' => $tweet_data['language'],
                        'location' => $tweet_data['location'],
                        'category' => $tweet_data['category']
                    ]
                );
                $this->info("Stored tweet: {$tweet_data['text']}");
            } catch (\Exception $e) {
                $this->error("Error processing tweet: {$e->getMessage()}");
            }
        });

        $this->info('Streaming stopped.');
    }
}