<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FanFavorite extends Model
{
    protected $table = 'fan_favorite';

    protected $fillable = [
        'user_id',
        'fan_id',
    ];
}
