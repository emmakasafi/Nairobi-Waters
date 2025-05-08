<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;

class WaterSentiment extends Model
{
    use Searchable;

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    protected $fillable = [
        'original_caption',
        'processed_caption',
        'timestamp',
        'overall_sentiment',
        'complaint_category',
        'source',
        'subcounty',
        'ward',
        'user_id',
        'user_name',
        'user_email',
        'user_phone',
        'status',
        'entity_type',
        'entity_name',
        'assigned_to',
        'department_id', // if you're associating this with a department
    ];

    public function toSearchableArray()
    {
        return [
            'original_caption'     => $this->original_caption,
            'processed_caption'    => $this->processed_caption,
            'timestamp'            => optional($this->timestamp)->toDateTimeString(),
            'overall_sentiment'    => $this->overall_sentiment,
            'complaint_category'   => $this->complaint_category,
            'source'               => $this->source,
            'subcounty'            => $this->subcounty,
            'ward'                 => $this->ward,
            'user_name'            => $this->user_name,
            'user_email'           => $this->user_email,
            'user_phone'           => $this->user_phone,
            'status'               => $this->status,
            'entity_type'          => $this->entity_type,
            'entity_name'          => $this->entity_name,
        ];
    }

    /**
     * The officer assigned to this complaint.
     */
    public function assignedOfficer()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * The customer who submitted the complaint.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Optional: Department this complaint belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
