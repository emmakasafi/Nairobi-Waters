<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use App\Models\WaterSentiment;
use Illuminate\Support\Facades\Http;

class FetchEmails extends Command
{
    protected $signature = 'fetch:emails';
    protected $description = 'Fetch water-related complaints from email and analyze them';

    public function handle()
    {
        $this->info("Connecting to email...");

        $client = Client::account('default');

        try {
            $client->connect();
            $this->info("Connected successfully.");
        } catch (\Exception $e) {
            $this->error("Failed to connect: " . $e->getMessage());
            return;
        }

        $folder = $client->getFolder('INBOX');
        $messages = $folder->messages()->unseen()->get();
        
        if ($messages->count() === 0) {
            $this->info("No new emails found.");
            return;
        }

        $this->info("Checking " . $messages->count() . " new emails...");

        $processedCount = 0;

        foreach ($messages as $message) {
            $subject = $message->getSubject();
            $body = $message->getTextBody();

            if ($this->isWaterComplaint($subject, $body, $message)) {
                $this->info("✅ Water-related email found: $subject");
                $this->analyzeAndStore($body, $message);
                $processedCount++;
                $message->setFlag('Seen'); // Mark email as read
            } else {
                $this->info("❌ Not a water-related email: $subject");
            }
        }

        if ($processedCount === 0) {
            $this->info("No water-related complaints found.");
        } else {
            $this->info("Finished processing $processedCount water-related complaints.");
        }
    }

    private function isWaterComplaint($subject, $body, $message)
    {
        // Combine subject and body for keyword check
        $body = $body ?: $message->getHTMLBody() ?: '';
        $keywords = ['water', 'billing', 'shortage', 'leakage', 'sewer', 'meter', 'supply', 'pressure', 'pipeline', 'contamination', 'wastewater'];
        
        foreach ($keywords as $keyword) {
            if (stripos($subject, $keyword) !== false || stripos($body, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    private function analyzeAndStore($complaintText, $message)
    {
        // Use HTML body or subject if text body is empty
        $complaintText = $complaintText ?: $message->getHTMLBody() ?: $message->getSubject();
        $complaintText = strip_tags($complaintText);
        $this->info("Sending complaint for analysis: '$complaintText'");

        if (empty($complaintText)) {
            $this->error("❌ Complaint text is empty before sending to API");
            return;
        }

        try {
            // Send as form data instead of JSON
            $response = Http::asForm()->timeout(60)->post('http://127.0.0.1:5001/analyze', [
                'complaint' => $complaintText,
                'user_email' => $message->getFrom()[0]->mail ?? 'default@example.com',
                'user_name' => $message->getFrom()[0]->personal ?? 'Unknown',
                'user_phone' => '',
                'subcounty' => '',
                'ward' => '',
                'entity_type' => 'individual',
                'entity_name' => $message->getFrom()[0]->personal ?? 'Unknown',
                'source' => 'email'
            ]);

            $this->info("Response status: " . $response->status());
            $this->info("Response body: " . $response->body());

            if ($response->successful()) {
                $data = $response->json();

                $sentimentMap = [
                    'positive' => 'pos',
                    'negative' => 'neg',
                    'neutral' => 'neu'
                ];
                $shortSentiment = $sentimentMap[$data['sentiment']] ?? 'neu';

                WaterSentiment::create([
                    'original_caption' => substr($complaintText, 0, 255),
                    'processed_caption' => substr($data['processed_caption'], 0, 255),
                    'timestamp' => now(),
                    'overall_sentiment' => $shortSentiment,
                    'complaint_category' => substr($data['category'], 0, 100),
                    'source' => 'email',
                    'subcounty' => '',
                    'ward' => '',
                    'user_id' => 1, // Default user_id, aligned with Flask
                    'user_name' => $message->getFrom()[0]->personal ?? 'Unknown',
                    'user_email' => $message->getFrom()[0]->mail ?? 'default@example.com',
                    'user_phone' => '',
                    'status' => 'pending',
                    'entity_type' => 'individual',
                    'entity_name' => $message->getFrom()[0]->personal ?? 'Unknown',
                    'department_id' => $data['department_id']
                ]);

                $this->info("✅ Complaint stored successfully with sentiment: $shortSentiment");
            } else {
                $this->error("❌ Failed to analyze complaint. Status: " . $response->status() . ", Body: " . $response->body());
            }
        } catch (\Exception $e) {
            $this->error("❌ Error analyzing complaint: " . $e->getMessage());
        }
    }
}