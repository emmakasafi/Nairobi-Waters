<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusUpdate extends Model
{
    protected $fillable = [
        'water_sentiment_id',
        'officer_id',
        'old_status',
        'new_status',
        'officer_notes',
        'status',
        'rejection_reason',
        'created_at',
        'updated_at',
    ];

    public function waterSentiment()
    {
        return $this->belongsTo(WaterSentiment::class);
    }

    public function officer()
    {
        return $this->belongsTo(User::class, 'officer_id');
    }
}