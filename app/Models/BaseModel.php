<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DateTimeInterface;

class BaseModel extends Model
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getCreatedAtAttribute($value)
    {
        return ($value) ? Carbon::parse($value)->format('Y/m/d H:i:s') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return ($value) ? Carbon::parse($value)->format('Y/m/d H:i:s') : null;
    }

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
