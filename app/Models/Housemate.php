<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Housemate extends Model
{
     use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.housemates');
       
    }

    protected $fillable = [
        'property_id','status'
    ];

    public function images()
    {
        return $this->morphMany(Image::class, 'taggable');
    }

    public function questionsanswer(){
        return $this->hasMany(QuestionAnswerUser::class,'housemate_id','id');
    }

    public function property(){
        return $this->belongsTo(Property::class,'property_id','id');
    }

    // Accessor 
    public function getStatusAttribute()
    {
        return $this->attributes['status'] == '1' ? 1 : 0;
    }

    // Mutator 
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower($value) == '1' ? 1 : 0;
    }
    
}
