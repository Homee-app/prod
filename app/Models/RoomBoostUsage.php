<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomBoostUsage extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.room_boost_usages');
    }

    
    protected $fillable = [
        'room_id',
        'used_at',
        'expires_at'
    ];

}
