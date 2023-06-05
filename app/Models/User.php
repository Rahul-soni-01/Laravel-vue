<?php

namespace App\Models;

use App\Define\CommonDefine;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Carbon;
use DateTimeInterface;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_first_login',
        'register_token_verify',
        'status',
        'confirm_status',
        'is_notification',
        'role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    protected static function booted()
    {
        static::deleting(function (User $user) {
            $user->userInfo()->delete();
        });
    }

    public function getCreatedAtAttribute($value)
    {
        return ($value) ? Carbon::parse($value)->format('Y/m/d H:i:s') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return ($value) ? Carbon::parse($value)->format('Y/m/d H:i:s') : null;
    }

    public function userInfo()
    {
        return $this->hasOne(UserInfo::class, 'user_id');
    }

    public function fileUser()
    {
        return $this->hasMany(File::class, 'user_id');
    }

    public function post()
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    /**
     *@return BelongsToMany
     */
    public function fan(): BelongsToMany
    {
        return $this->belongsToMany(
            Fan::class,
            'fan_user',
            'user_id',
            'fan_id'
        )
            ->where('fan_user.status', CommonDefine::PAYMENT_SUCCESS)
            ->withPivot('created_at');
    }

    /**
     * @return HasMany
     */
    public function product(): HasMany
    {
        return $this->hasMany(Product::class, 'id');
    }

    /**
     * @return BelongsToMany
     */
    public function productFavorite(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_favorite', 'product_id', 'user_id');
    }

    /**
     * @return HasMany
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * @return BelongsToMany
     */
    public function postFavorite(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_favorite', 'user_id', 'post_id');
    }

    /**
     * @return BelongsToMany
     */
    public function productPayments(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_payment', 'user_id', 'product_id');
    }

    public function plan(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_user', 'user_id', 'plan_id');
    }

    public function ownerFan()
    {
        return $this->hasOne(Fan::class, 'author_id');
    }
}
