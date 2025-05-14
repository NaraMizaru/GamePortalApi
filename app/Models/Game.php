<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'games';

    protected $fillable = [
        'slug',
        'title',
        'description',
        'created_by',
    ];

    protected $hidden = [
        'id',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'users'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function gameVersions()
    {
        return $this->hasMany(GameVersion::class);
    }

    public function latestGameVersion()
    {
        return $this->hasOne(GameVersion::class)->latestOfMany();
    }
}
