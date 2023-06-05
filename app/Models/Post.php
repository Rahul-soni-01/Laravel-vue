<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends BaseModel
{
    use HasFactory;
    protected $table = 'posts';

    protected $fillable = [
        'id',
        'title',
        'content',
        'category_id',
        'author_id',
        'url_file',
        'url_file_video',
        'is_public',
        'status',
        'plan_id',
        'auto_public',
        'date_public',
        'created_at'
    ];

    protected $hidden = ['pivot'];

    protected static function booted()
    {
        static::deleting(function (Post $post) {
            $post->tags()->detach();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function userFavorite()
    {
        return $this->belongsToMany(User::class, 'post_favorite', 'post_id', 'user_id');
    }
}
