<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Notifications\Notifiable;

// class User extends Authenticatable
// {
//     use HasFactory, Notifiable;

//     protected $fillable = [
//         'name',
//         'email',
//         'password',
//         'email_verified_at',
//         'remember_token',
//     ];

//     public function hasRole($role)
//     {
//         return $this->role === $role;
//     }

//     protected $hidden = [
//         'password',
//         'remember_token',
//     ];

//     protected function casts(): array
//     {
//         return [
//             'email_verified_at' => 'datetime',
//             'password' => 'hashed',
//         ];
//     }

//     public function tests()
//     {
//         return $this->belongsToMany(Test::class, 'test_user')->withTimestamps();
//     }
// }

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'password'];

    protected function setPasswordAttribute($value){
        $this->attributes['password'] = bcrypt($value);
    }

    // Admin has many Tests
    public function tests()
    {
        return $this->hasMany(Test::class);
    }
}
