<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwitterData extends Model
{
    protected $table = 'twitter_data';
    protected $primaryKey = 'tweet_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tweet_id',
        'text',
        'created_at',
        'user_handle',
        'sentiment_score',
        'sentiment_label',
        'keywords',
        'language',
        'location',
        'category'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'sentiment_score' => 'float'
    ];
}