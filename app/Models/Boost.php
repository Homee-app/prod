<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boost extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.boosts');
    }

    protected $fillable = [
        'user_id',
        'boost_count'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
