<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanUser extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'plan_user';

    protected $fillable = [
        'id',
        'plan_id',
        'user_id',
        'created_at',
        'payment_date',
        'type',
        'status',
        'payment_price',
        'reason',
        'telno',
        'email',
        'expired_date',
        'date_out',
        'progress_payment'
    ];

    public $timestamps = true;

    /**
     * @return BelongsTo;
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * @return BelongsTo;
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
