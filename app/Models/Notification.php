<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends BaseModel
{
    use HasFactory, SoftDeletes;
    protected $table = 'notification_histories';

    protected $fillable = [
        'content',
        'user_id',
        'is_read',
        'type',
        'created_by',
        'fan_id',
        'product_id',
        'post_id'
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
