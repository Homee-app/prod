<?php

namespace App\Models; 

use Illuminate\Database\Eloquent\Model;

class Suburb extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.suburbs');
       
    }

}
