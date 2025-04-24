<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WaterSentiment;
use Illuminate\Support\Facades\DB;

class RandomizeOnlineFormEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:randomize-online-forms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Randomize data for entries with "Online Form" as the source';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Sample data for randomization with Nairobi County context
        $userNames = ['John Mwangi', 'Jane Wairimu', 'Alice Omondi', 'Bob Otieno', 'Mary Achieng', 'Peter Kimani', 'Susan Muthoni', 'David Oduor', 'Lillian Mwende', 'James Ouma'];
        $userEmails = ['john.mwangi@example.com', 'jane.wairimu@example.com', 'alice.omondi@example.com', 'bob.otieno@example.com', 'mary.achieng@example.com', 'peter.kimani@example.com', 'susan.muthoni@example.com', 'david.oduor@example.com', 'lillian.mwende@example.com', 'james.ouma@example.com'];
        $userPhones = ['0712345678', '0723456789', '0734567890', '0745678901', '0756789012', '0767890123', '0778901234', '0789012345', '0790123456', '0701234567'];
        $statuses = ['Pending', 'Resolved', 'In Progress'];
        $entityTypes = ['Individual', 'Estate', 'School', 'Hospital'];
        $entityNames = [
            'Kenyatta Estate', 'Nairobi School', 'Kenyatta Hospital', 'Mombasa Estate', 
            'Eldoret University', 'Kisumu Hospital', 'Nakuru Estate', 
            'Ruaraka Estate', 'Roysambu School', 'Kasarani Hospital', 
            'Langata Estate', 'Embakasi South School', 'Dagoretti North Hospital', 
            'Westlands Estate', 'Kibra School', 'Embakasi North Hospital', 
            'Starehe Estate', 'Mathare School', 'Makadara Hospital', 
            'Kamukunji Estate', 'Pumwani School'
        ];
        $mediaLinks = [
            'https://example.com/media/1', 'https://example.com/media/2', 
            'https://example.com/media/3', 'https://example.com/media/4', 
            'https://example.com/media/5', 'https://example.com/media/6', 
            'https://example.com/media/7', 'https://example.com/media/8', 
            'https://example.com/media/9', 'https://example.com/media/10'
        ];

        // Fetch entries with "Online Form" as the source
        $entries = WaterSentiment::where('source', 'Online Form')->get();

        foreach ($entries as $entry) {
            $entry->user_id = rand(1, 100); // Random user ID
            $entry->user_name = $userNames[array_rand($userNames)];
            $entry->user_email = $userEmails[array_rand($userEmails)];
            $entry->user_phone = $userPhones[array_rand($userPhones)];
            $entry->status = $statuses[array_rand($statuses)];
            $entry->entity_type = $entityTypes[array_rand($entityTypes)];
            $entry->entity_name = $entityNames[array_rand($entityNames)];
            $entry->media_links = $mediaLinks[array_rand($mediaLinks)];
            $entry->save();
        }

        $this->info('Data randomized successfully for Online Form entries.');

        return Command::SUCCESS;
    }
}