<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantProfile extends Model
{
    protected $table;

    protected $fillable = [
        'user_id',
        'introduction',
        'ethnicity',
        'religious_beliefs',
        'employment',
        'profile_photo_url',
        'lifestyle',
        'habits',
        'know_them_better_prompts',
        'interests',
        'preferences',
        'age',
        'gender',
        'is_teamup',
        'availability',
        'intended_stay_duration',
    ];

    protected $casts = [
        'lifestyle' => 'array',
        'habits' => 'array',
        'know_them_better_prompts' => 'array',
        'interests' => 'array',
        'preferences' => 'array',
        'is_teamup' => 'boolean',
        'availability' => 'date',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.tenant_profiles');
       
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
