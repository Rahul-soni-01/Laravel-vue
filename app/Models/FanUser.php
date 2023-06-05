<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class FanUser extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'fan_user';

    protected $fillable = ['id', 'fan_id', 'status', 'user_id', 'created_at'];

    public $timestamps = true;
}
