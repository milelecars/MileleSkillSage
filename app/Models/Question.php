<?php

// namespace App\Imports;

// use App\Models\Question;
// use App\Models\Test;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;

// class QuestionsImport implements ToModel, WithHeadingRow
// {
//     protected $test;

//     public function __construct(Test $test)
//     {
//         $this->test = $test;
//     }

//     public function model(array $row)
//     {
//         return new Question([
//             'test_id' => $this->test->id,
//             'question' => $row['question'],
//             'image_url' => $row['image_url'] ?? null,
//             'choice_a' => $row['choice_a'],
//             'choice_b' => $row['choice_b'],
//             'choice_c' => $row['choice_c'],
//             'choice_d' => $row['choice_d'],
//             'correct_answer' => $row['answer'],
//         ]);
//     }
// }

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id', 'question_text', 'question_type', 'category', 'reverse', 'red_flag'
    ];

    // Question has many Choices
    public function choices()
    {
        return $this->hasMany(QuestionChoice::class);
    }

    // Question has many Media items
    public function media()
    {
        return $this->hasOne(QuestionMedia::class);
    }

    // Question belongs to a Test
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    // One question can have many answers 
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
