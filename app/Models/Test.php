<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];
    public function invitation()
    {
        return $this->hasOne(TestInvitation::class);
    }

    // Define relationships, if any
    public function users()
    {
        return $this->belongsToMany(User::class, 'test_user')->withTimestamps();
    }

    public function candidates()
    {
        return $this->belongsToMany(Candidate::class, 'test_user')->withTimestamps();
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // Add any other methods or relationships you may need
}
