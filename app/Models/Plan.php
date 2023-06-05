<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'plans';

    protected $fillable = [
        'id',
        'title',
        'sub_title',
        'fan_id',
        'price',
        // 'discount',
        'discount_code',
        'note',
        'photo',
        'type',
        'pro_stripe_id',
        'price_stripe_id',
        'product_stripe',
        'price_stripe',
        'price_year',
    ];

    public $timestamps = true;

    protected static function booted()
    {
        static::deleting(function (Plan $plan) {
            $plan->users()->detach();
        });
    }

    /**
     * @return BelongsTo
     */
    public function fan(): BelongsTo
    {
        return $this->belongsTo(Fan::class, 'fan_id');
    }

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'plan_user', 'plan_id', 'user_id')->withPivot('id', 'status', 'reason', 'payment_date', 'expired_date', 'updated_at', 'date_out', 'type');
    }
}
