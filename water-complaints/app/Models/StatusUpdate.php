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
        'requires_customer_confirmation',
        'status',
        'customer_confirmed_at',
        'customer_rejection_reason',
        'customer_responded_at',
        'rejection_reason',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'requires_customer_confirmation' => 'boolean',
        'customer_confirmed_at' => 'datetime',
        'customer_responded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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