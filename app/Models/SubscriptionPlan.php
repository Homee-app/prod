<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.subscription_plans');
    }

    protected $fillable = [
        'name',
        'product_id',
        'price',
        'value',
        'value_type',
        'type',
        'platform',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions()
    {
        return $this->HasMany(Subscription::class);
    }

    public function getIntervalMethodAttribute()
    {
        if (is_production()) {
            return match ($this->value_type) {
                'day'   => 'addDays',
                'week'  => 'addWeeks',
                'month' => 'addMonths',
                default => 'addDays',
            };
        }

        return match ($this->value_type) {
            'day'   => fn($date) => $date->addMinutes(5),
            'week'  => fn($date) => $date->addMinutes(10),
            'month' => fn($date) => $date->addMinutes(15),
            default => fn($date) => $date->addMinutes(5),
        };
    }

    public function getExpiryDateAttribute()
    {
        if (is_production()) {
            return match ($this->value_type) {
                'day'   => Carbon::now()->addMinutes($this->value),
                'week'  => Carbon::now()->addMinutes($this->value),
                'month' => Carbon::now()->addMinutes($this->value),
                default => Carbon::now()->addMinutes($this->value),
            };
        }
        return match ($this->value_type) {
            'day'   => Carbon::now()->addMinutes(5),
            'week'  => Carbon::now()->addMinutes(10),
            'month' => Carbon::now()->addMinutes(15),
            default => Carbon::now()->addMinutes(5),
        };
    }
}
