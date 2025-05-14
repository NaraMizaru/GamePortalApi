<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;

    protected $table = 'scores';

    protected $filable = [
        'user_id',
        'game_version_id',
        'score',
    ];
}
