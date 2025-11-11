<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.subscriptions');
    }

    protected $fillable = [
        'user_id',
        'product_id',
        'plan_id',
        'started_at',
        'expires_at',
        'user_role',
        'status',
        'amount'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function role()
    {
        return $this->belongsTo(Roles::class, 'user_role');
    }
}
