<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;

class WaterSentiment extends Model
{
    use Searchable;

    public function toSearchableArray()
    {
        return [
            'original_caption' => $this->original_caption,
            'processed_caption' => $this->processed_caption,
            'timestamp' => $this->timestamp,
            'overall_sentiment' => $this->overall_sentiment,
            'complaint_category' => $this->complaint_category,
            'source' => $this->source,
            'subcounty' => $this->subcounty,
            'ward' => $this->ward,
        ];
    }
}