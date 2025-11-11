<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserKeyUsage extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.user_key_usages');
    }
    
    protected $fillable = [
        'user_id',
        'used_at',
    ];
}
