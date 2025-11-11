<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscribedTransaction extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.subscribed_trans_ids');
    }

}
