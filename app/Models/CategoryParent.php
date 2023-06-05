<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryParent extends BaseModel
{
    use HasFactory;

    protected $table = 'category_parent';

    protected $fillable = [
        'name',
    ];

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
