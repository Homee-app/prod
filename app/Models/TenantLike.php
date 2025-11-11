<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantLike extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.tenant_likes_list');
       
    }

}
