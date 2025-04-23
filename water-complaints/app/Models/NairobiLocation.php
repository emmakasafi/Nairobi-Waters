<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NairobiLocation extends Model
{
    use HasFactory;

    
    protected $table = 'nairobi_locations';

    
    protected $fillable = ['subcounty', 'ward'];
}
