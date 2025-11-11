<?php

namespace App\Models;

use Database\Seeders\QuestionOptionSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    //

    protected $table;
    use SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.questions');
       
    }

    protected $fillable = [
        'title_for_web',
        'title_for_app',
        'sub_title_for_app',
        'type_for_app',
        'type_for_web',
        'showing_in',
        'question_order_for_web',
        'question_order_for_app',
    ];
    
    public function options()
    {
        return $this->hasMany(QuestionsOption::class, 'question_id');
    }

    public function answers()
    {
        return $this->hasMany(QuestionAnswerUser::class, 'question_id');
    }

    public function userAnswer()
    {
        return $this->hasMany(QuestionAnswerUser::class, 'question_id');
    }

    public function partnerAnswer()
    {
        return $this->hasMany(QuestionAnswerUser::class, 'question_id')
            ->where('for_partner', true);
    }

    public function privacySetting()
    {
        return $this->hasOne(UserQuestionPrivacy::class, 'question_id');
    }


}
