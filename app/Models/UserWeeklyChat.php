<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWeeklyChat extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.user_weekly_chats');
    }
    protected $fillable = [
        'user_id',
        'chat_count',
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
