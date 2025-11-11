<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionAnswerUser extends Model
{
    //

    protected $table;
    use SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.question_answers_user');
    }
    protected $fillable = [
        'user_id',
        'for_partner',
        'question_id',
        'option_id',
        'answer',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }


    public function option()
    {
        return $this->belongsTo(QuestionsOption::class, 'option_id', 'id');
    }

    public function suburbs()
    {
        return $this->belongsToMany(Suburb::class, 'question_answers_user', 'question_id', 'option_id', 'question_id', 'id')
            ->wherePivot('question_id', 20);
    }

    public function suburbDetails()
    {
        return $this->belongsTo(Suburb::class, 'option_id');
    }

}
