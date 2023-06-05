<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class MessageDetail extends BaseModel
{
    use HasFactory;

    protected $table = 'message_detail';

    protected $fillable = [
        'id',
        'content',
        'message_id',
        'user_id',
        'receiver_id',
        'url',
        'url_type'
    ];

    /**
     * @return BelongsTo
     */
    public function message() : BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_id');
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

    public $timestamps = true;
}
