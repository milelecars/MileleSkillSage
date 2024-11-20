<?php

// namespace App\Models;

// use Illuminate\Support\Carbon;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Factories\HasFactory;

// class TestInvitation extends Model
// {
//     use HasFactory;

//     protected $table = 'test_invitations';

//     protected $fillable = ['test_id', 'invitation_link', 'invitation_token', 'email_list', 'expires_at', 'created_by'];

//     protected $casts = [
//         'email_list' => 'array',  
//         'expires_at' => 'datetime',
//     ];

//     public function test()
//     {
//         return $this->belongsTo(Test::class, 'test_id');
//     }

//     public function creator()
//     {
//         return $this->belongsTo(User::class, 'created_by');
//     }

    
//     public function scopeValid($query)
//     {
//         return $query->where('expires_at', '>', now());
//     }

//     public function isExpired()
//     {
        
//         return $this->expires_at && Carbon::parse($this->expires_at)->isPast();
//     }

    
//     public function hasEmail($email)
//     {
//         return in_array($email, $this->email_list);
//     }
// }

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

    // Invitation belongs to a Test
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
