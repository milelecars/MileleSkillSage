<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestInvitation extends Model
{
    use HasFactory;

    protected $table = 'test_invitations';

    protected $fillable = [
        'test_id',
        'invitation_link',
        'email_list',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'email_list' => 'array',
        'expires_at' => 'datetime',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope to get only valid (non-expired) invitations
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    // Check if an email is in the email_list
    public function hasEmail($email)
    {
        return in_array($email, $this->email_list);
    }
}