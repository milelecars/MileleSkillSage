<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;

class Candidate extends Authenticatable 
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'test_started_at',
        'test_completed_at',
        'test_score',
    ];

    public function tests()
    {
        return $this->belongsToMany(Test::class, 'test_user')->withTimestamps();
    }

    public function userResponses()
    {
        return $this->hasMany(UserResponse::class); // Assuming UserResponse belongs to Candidate
    }
}
