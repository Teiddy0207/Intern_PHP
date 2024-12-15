<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    // Trả về định danh của JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // Trả về các claims tùy chỉnh
    public function getJWTCustomClaims(array $args = [])
    {
        return [];
    }
}
