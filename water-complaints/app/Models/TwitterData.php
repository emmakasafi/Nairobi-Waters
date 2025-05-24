<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwitterData extends Model
{
    protected $table = 'twitter_data';

    protected $fillable = [
        'original_caption',
        'processed_caption',
        'timestamp',
        'overall_sentiment',
        'complaint_category',
    ];

    protected $dates = ['timestamp'];
}