<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostFavorite extends Model
{
    use HasFactory;

    protected $table = 'post_favorite';

    protected $fillable = [
        'user_id',
        'post_id',
    ];
}
