<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TwitterData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TwitterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csvFile = fopen('C:\\Users\\emmah\\Downloads\\result (1)\\water_sentiments.csv', 'r');
        $firstLine = true;
        while (($data = fgetcsv($csvFile, 2000, ',')) !== FALSE) {
            if (!$firstLine) {
                // Ensure the row has enough columns
                if (count($data) < 6) {
                    continue; // Skip this row if it doesn't have enough columns
                }

                // Extract columns safely
                $id = isset($data[0]) ? $data[0] : null;
                $original_caption = isset($data[1]) ? $data[1] : null;
                $processed_caption = isset($data[2]) ? $data[2] : null;
                $timestamp = isset($data[3]) && $data[3] ? date('Y-m-d H:i:s', strtotime($data[3])) : $this->generateRandomDate();
                $overall_sentiment = isset($data[4]) ? $data[4] : null;
                $complaint_category = isset($data[5]) ? $data[5] : null;

                // Insert the data into the database
                TwitterData::create([
                    'id' => $id,
                    'original_caption' => $original_caption,
                    'processed_caption' => $processed_caption,
                    'timestamp' => $timestamp,
                    'overall_sentiment' => $overall_sentiment,
                    'complaint_category' => $complaint_category,
                ]);
            }
            $firstLine = false;
        }
        fclose($csvFile);
    }

    /**
     * Generate a random date for the year 2024.
     *
     * @return string
     */
    private function generateRandomDate(): string
    {
        $start = Carbon::createFromDate(2024, 1, 1);
        $end = Carbon::createFromDate(2024, 12, 31);
        $randomTimestamp = rand($start->timestamp, $end->timestamp);
        $randomDate = Carbon::createFromTimestamp($randomTimestamp);
        return $randomDate->toDateTimeString();
    }
}