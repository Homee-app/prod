<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionsOption extends Model
{

    protected $table;
    use SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.question_options');
       
    }
    public function questions()
    {
        return $this->hasMany(Question::class, 'question_order_for_app');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'taggable');
    }
}
