<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    // Department belongs to many Tests (pivot table: candidate_test)
    public function tests()
    {
        return $this->belongsToMany(Test::class, 'candidate_test')
        ->withPivot(['started_at', 'completed_at', 'score', 'red_flags', 'correct_answers', 'wrong_answers','ip_address', 'status'])
        ->withTimestamps();
    }
}
