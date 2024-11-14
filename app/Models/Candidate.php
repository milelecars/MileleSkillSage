<?php

// namespace App\Models;

// use App\Models\Test;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Foundation\Auth\User as Authenticatable;

// class Candidate extends Authenticatable
// {
//     use HasFactory, Notifiable;

//     protected $fillable = [
//         'name', 'email', 'email_verified_at', 'test_started_at', 'test_completed_at', 'test_answers', 'test_score', 'test_name'
//     ];
    
//     protected $hidden = [
//         'remember_token',
//     ];

//     protected $casts = [
//         'email_verified_at' => 'datetime',
//         'test_started_at' => 'datetime',
//         'test_completed_at' => 'datetime',
//         'test_answers' => 'array',
//     ];

//     public function tests()
//     {
//         return $this->belongsToMany(Test::class, 'test_candidate')
//             ->withTimestamps()
//             ->withPivot(['started_at', 'completed_at', 'answers']);
//     }
    

// }

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Candidate extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['name', 'email'];

    // Candidate has many Reports
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // Candidate has many Answers
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    // Candidate has many Flags
    public function candidateFlags()
    {
        return $this->hasMany(CandidateFlag::class);
    }

    // Candidate takes many Tests (pivot table: candidate_test)
    public function tests()
    {
        return $this->belongsToMany(Test::class, 'candidate_test')
            ->withPivot(['started_at', 'completed_at', 'score'])
            ->withTimestamps();
    }
}

