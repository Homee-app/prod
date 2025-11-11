<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserIdentity extends Model
{
    // use HasFactory;

    protected $fillable = [
        'user_id',
        'id_type',
        'front_of_id_path',
        'back_of_id_path',
        'verification_status',
        'rejection_reason',
    ];

    protected $table = 'user_identities';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.user_identities');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
