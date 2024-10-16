<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'questions_file_path'];
    
    public function invitation()
    {
        return $this->hasOne(TestInvitation::class);
    }

    // Define relationships, if any
    public function users()
    {
        return $this->belongsToMany(User::class, 'test_user')->withTimestamps();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($test) {
            // Delete associated invitation
            $test->invitation()->delete();
            
            $test->users()->detach();
        });
    }

    // Add any other methods or relationships you may need
}
