<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoldenKey extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.golden_keys');
    }

    protected $fillable = [
        'user_id' ,'key_count',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

}
