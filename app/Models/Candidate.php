<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        // Add any other columns you may need
    ];

    public function tests()
    {
        return $this->belongsToMany(Test::class, 'test_user')->withTimestamps();
    }

    public function userResponses()
    {
        return $this->hasMany(UserResponse::class); // Assuming UserResponse belongs to Candidate
    }
}
