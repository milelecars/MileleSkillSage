<?php

// namespace App\Models;

// use App\Models\User;
// use App\Models\Candidate;
// use App\Models\TestInvitation;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Factories\HasFactory;

// class Test extends Model
// {
//     use HasFactory;

//     protected $fillable = ['name', 'duration', 'description',  'questions_file_path'];

//     public function invitation()
//     {
//         return $this->hasOne(TestInvitation::class);
//     }

//     public function users()
//     {
//         return $this->belongsToMany(User::class, 'test_user')->withTimestamps();
//     }

//     public function candidates()
//     {
//         return $this->belongsToMany(Candidate::class, 'test_candidate')
//             ->withTimestamps()
//             ->withPivot(['started_at', 'completed_at', 'answers', 'score']);
//     }

//     protected static function boot()
//     {
//         parent::boot();
//         static::deleting(function ($test) {
//             $test->invitation()->delete();
//             $test->candidates()->detach();
//         });
//     }

//     public function calculateEndTime($startTime)
//     {
//         return Carbon::parse($startTime)->addMinutes($this->duration);
//     }
// }


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'duration', 'invitation_link', 'admin_id', 'overall_results_pdf_path'
    ];

    // Test has many Questions
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // Test has one Invitation
    public function invitation()
    {
        return $this->hasOne(Invitation::class);
    }

    // Test has many Candidates through a pivot table
    public function candidates()
    {
        return $this->belongsToMany(Candidate::class, 'candidate_test')
            ->withPivot(['started_at', 'completed_at', 'score'])
            ->withTimestamps();
    }

    // Test belongs to an Admin (the admin who created it)
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
