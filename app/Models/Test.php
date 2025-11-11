<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.test','test');
    }

    protected $fillable = [
        'data'
    ] ;

    protected $casts = [
        'data' => 'json'
    ];

}
