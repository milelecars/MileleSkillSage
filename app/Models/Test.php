<?php

namespace App\Models;

use App\Models\User;
use App\Models\Candidate;
use App\Models\TestInvitation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Test extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'duration', 'description',  'questions_file_path'];

    public function invitation()
    {
        return $this->hasOne(TestInvitation::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'test_user')->withTimestamps();
    }

    public function candidates()
    {
        return $this->belongsToMany(Candidate::class, 'test_candidate')
            ->withTimestamps()
            ->withPivot(['started_at', 'completed_at', 'answers', 'score', 'monitoring_data']);
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($test) {
            $test->invitation()->delete();
            $test->users()->detach();
            $test->candidates()->detach();
        });
    }

    public function calculateEndTime($startTime)
    {
        return Carbon::parse($startTime)->addMinutes($this->duration);
    }
}