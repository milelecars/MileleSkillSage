<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id', 'invited_emails', 'expiration_date', 'invitation_token', 'invitation_link'
    ];

    protected $casts = [
        'invited_emails' => 'array',  
        'expiration_date' => 'datetime',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    
    public function scopeValid($query)
    {
        return $query->where('expiration_date', '>', now());
    }

    public function isExpired()
    {
        
        return $this->expiration_date && Carbon::parse($this->expiration_date)->isPast();
    }

    
    public function hasInvited($email)
    {
        return in_array($email, $this->invited_emails);
    }
}
