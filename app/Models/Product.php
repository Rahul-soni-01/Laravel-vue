<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'id',
        'title',
        'content',
        'category_id',
        'author_id',
        'price',
        'is_public',
        'type',
        'view',
        'status',
        'pro_stripe_id',
        'price_stripe_id',
        'product_stripe',
        'price_stripe',
        'thumbnail_url',
        'auto_public',
        'date_public',
        'plan_id'
    ];

    protected $hidden = ['pivot'];

    protected static function booted()
    {
        static::deleting(function (Product $product) {
            $product->files()->detach();
            $product->users()->detach();
            $product->tags()->detach();
        });
    }

    /**
     * @return BelongsToMany
     */
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'product_files', 'product_id', 'file_id');
    }

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'product_favorite', 'product_id', 'user_id');
    }

    /**
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tags', 'product_id', 'tag_id');
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsToMany
     */
    public function usersFavorite(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'product_favorite', 'product_id', 'user_id');
    }

    /**
     * @return BelongsToMany
     */
    public function userPayments(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'product_payment', 'product_id', 'user_id')->withPivot('id', 'status', 'updated_at');
    }

    /**
     * @return BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}
