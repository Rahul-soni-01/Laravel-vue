<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'files';

    protected $fillable = ['user_id', 'user_file_type', 'url', 'name'];

    protected $hidden = ['pivot'];
}
