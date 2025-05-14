<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Administrator extends Authenticatable
{
    use HasFactory, HasApiTokens;

    public $table = 'administrators';

    protected $fillable = [
        'username',
        'password',
        'last_login_at',
    ];
}
