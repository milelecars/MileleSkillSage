<?php

namespace App\Models;

use App\Models\Test;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Candidate extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'email_verified_at', 'test_started_at', 'test_completed_at', 'test_answers', 'test_score', 'test_name'
    ];
    
    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'test_started_at' => 'datetime',
        'test_completed_at' => 'datetime',
        'test_answers' => 'array',
    ];

    public function tests()
    {
        return $this->belongsToMany(Test::class, 'candidate_test')
            ->withPivot(['started_at', 'completed_at', 'score','ip_address'])
            ->withTimestamps();
    }
    

}