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

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'test_started_at' => 'datetime',
        'test_completed_at' => 'datetime',
    ];

    public function userResponses()
    {
        return $this->hasMany(UserResponse::class);
    }
}