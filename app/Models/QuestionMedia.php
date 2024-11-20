<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id', 'image_url', 'description'
    ];

    // Media belongs to a Question
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
