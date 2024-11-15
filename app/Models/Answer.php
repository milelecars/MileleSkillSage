<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id', 
        'question_id', 
        'answer_text'
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
