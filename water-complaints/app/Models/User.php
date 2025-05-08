<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id', // Ensure this is present if you associate users with departments
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Water sentiments assigned to this officer
     */
    public function assignedSentiments()
    {
        return $this->hasMany(WaterSentiment::class, 'assigned_to');
    }

    /**
     * Water sentiments submitted by this user
     */
    public function submittedSentiments()
    {
        return $this->hasMany(WaterSentiment::class, 'user_id');
    }
}
