<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    public $table = 'messages';

    protected $fillable = [
        'message',
        'user_id',
        'receiver_id',
        'is_read',
        'url_type',
        'created_by',
        'url',
        'delete_by'
    ];

    public static function booted()
    {
        static::deleting(function(Message $message) {
            $message->messageDetail()->delete();
        });
    }

    /**
     * @return BelongsTo
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * @return HasMany
     */
    public function messageDetail() : HasMany
    {
        return $this->hasMany(MessageDetail::class, 'message_id');
    }
}
