<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Candidate extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['name', 'email'];

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
        ->withPivot(['started_at', 'completed_at', 'score','ip_address'])
        ->withTimestamps();
    }

    
}

