<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id', 'test_id', 'flag_type_id', 'occurrences', 'is_flagged'
    ];

    // Flag belongs to a Candidate
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // Flag belongs to a Test
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    // Flag belongs to a FlagType
    public function flagType()
    {
        return $this->belongsTo(FlagType::class);
    }
}
