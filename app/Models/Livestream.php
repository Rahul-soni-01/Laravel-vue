<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class Livestream extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'livestreams';

    protected $fillable = [
        'title',
        'author_id',
        'image_thumbnail',
        'status',
        'save_history',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'author_id');
    }

    /**
     * @return BelongsToMany
     */
    public function plan(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_livestreams', 'livestream_id', 'plan_id');
    }
}
