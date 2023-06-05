<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fan extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'fans';

    protected $fillable = [
        'id',
        'title',
        'sub_title',
        'nickname',
        'category_id',
        'author_id',
        'photo',
        'avt',
        'background',
        'status',
        'content',
        'brand_id'
    ];

    /**
     *@return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'fan_user',
            'fan_id',
            'user_id'
        )->withPivot(
            'created_at',
            'status'
        );
    }
    /**
     *@return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     *@return BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsToMany
     */
    public function userFavorite()
    {
        return $this->belongsToMany(User::class, 'fan_favorite', 'fan_id', 'user_id');
    }

    /**
     * @return HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'author_id', 'author_id')
            ->where('status', 1)
            ->where('is_public', 1)
            ->orderBy('view', 'desc')
            ->limit(30);
    }

    public function plans()
    {
        return $this->hasMany(Plan::class, 'fan_id');
    }
}
