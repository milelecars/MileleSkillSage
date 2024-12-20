<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Test extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 
        'description', 
        'duration', 
        'admin_id', 
        'overall_results_pdf_path',
        'deleted_by'  // Add this to track who deleted the test
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    // Existing relationships
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function questionChoices()
    {
        return $this->hasManyThrough(QuestionChoice::class, Question::class);
    }

    public function questionMedia()
    {
        return $this->hasManyThrough(QuestionMedia::class, Question::class);
    }

    public function candidates()
    {
        return $this->belongsToMany(Candidate::class, 'candidate_test')
            ->withPivot(['started_at', 'completed_at', 'score', 'ip_address', 'status'])
            ->withTimestamps();
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function invitation()
    {
        return $this->hasOne(Invitation::class);
    }

    // Add relationship to track who deleted the test
    public function deletedBy()
    {
        return $this->belongsTo(Admin::class, 'deleted_by');
    }

    // Scopes for easy querying
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('deleted_at');
    }

    // Helper methods
    public function isArchived()
    {
        return $this->deleted_at !== null;
    }

    public function archive($adminId)
    {
        $this->update([
            'deleted_by' => $adminId
        ]);
        $this->delete(); // This will set deleted_at
    }

    public function restore($force = false)
    {
        if ($force) {
            $this->update([
                'deleted_at' => null,
                'deleted_by' => null
            ]);
        } else {
            parent::restore();
        }
    }
}