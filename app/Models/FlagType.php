<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlagType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'threshold'
    ];

    // FlagType has many CandidateFlags
    public function candidateFlags()
    {
        return $this->hasMany(CandidateFlag::class);
    }
}
