<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'invitation_link', // Add any other columns that are in your migration
    ];

    // Define relationships, if any
    public function users()
    {
        return $this->belongsToMany(User::class, 'test_user')->withTimestamps();
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // Add any other methods or relationships you may need
}
