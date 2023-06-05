<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductFavorite extends BaseModel
{
    use HasFactory;

    protected $table = 'product_favorite';

    protected $fillable = [
        'id',
        'user_id',
        'product_id'
    ];
}
