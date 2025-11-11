<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    //

    protected $table;

    protected $fillable = [
        'user_id',
        'question_id',
        'option_id',
        'answer',
        'for_partner',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.question_answers_user');
       
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function option()
    {
        return $this->belongsTo(QuestionsOption::class, 'option_id'); 
    }

}
