<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserInfo extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_info';

    protected $fillable = [
        'id',
        'user_id',
        'full_name',
        'avt_url',
        'sex',
        'address',
        'language',
        'note',
        'category_favorite',
        'phone',
        'birth_day',
        'front_photo',
        'backside_photo'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
