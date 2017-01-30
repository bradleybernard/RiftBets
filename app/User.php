<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'facebook_id', 'verified', 'gender', 'avatar_url',
    ];
    
    protected $hidden = [
        'password', 'remember_token',
    ];
}
