<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQuestionPrivacy extends Model
{
    //

    protected $fillable = [
        'user_id',
        'question_id',
        'is_hidden',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.user_question_privacies');
       
    }

}
