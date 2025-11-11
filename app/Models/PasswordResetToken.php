<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{

    protected $table;
    public $timestamps = false;

    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['email', 'token', 'created_at'];


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.password_reset_tokens');
    }
}
