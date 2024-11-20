<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id', 'test_id', 'score', 'pdf_path', 'completion_status', 'date_completed'
    ];

    // Report belongs to a Candidate
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // Report belongs to a Test
    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
