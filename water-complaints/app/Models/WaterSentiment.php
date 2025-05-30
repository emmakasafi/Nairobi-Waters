<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class WaterSentiment extends Model
{
    use Searchable;

    // Disable Laravel's automatic timestamps
    public $timestamps = false;

    protected $casts = [
        'timestamp' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
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
        'department_id',
        'officer_notes',
        'pending_status_update_id',
        'resolved_at',
        'closed_at',
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
            'ward'               => $this->ward,
            'user_name'            => $this->user_name,
            'user_email'           => $this->user_email,
            'user_phone'           => $this->user_phone,
            'status'               => $this->status,
            'entity_type'          => $this->entity_type,
            'entity_name'          => $this->entity_name,
        ];
    }

    public function assignedOfficer()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function statusUpdates()
    {
        return $this->hasMany(StatusUpdate::class, 'water_sentiment_id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->user_id && $model->user_email) {
                $user = User::where('email', $model->user_email)->first();
                if ($user) {
                    $model->user_id = $user->id;
                    \Log::info('Synced user_id from user_email during creation', [
                        'water_sentiment_id' => $model->id ?? 'new',
                        'user_id' => $user->id,
                        'user_email' => $model->user_email,
                    ]);
                } else {
                    \Log::warning('No user found for user_email during creation', [
                        'water_sentiment_id' => $model->id ?? 'new',
                        'user_email' => $model->user_email,
                    ]);
                }
            }
        });
    }
}