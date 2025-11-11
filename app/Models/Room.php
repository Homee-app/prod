<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.rooms');
    }

    protected $fillable = [
        'property_id',
        'status'
    ];

    public function images()
    {
        return $this->morphMany(Image::class, 'taggable');
    }

    public function questionsanswer()
    {
        return $this->hasMany(QuestionAnswerUser::class, 'room_id', 'id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'id');
    }

    public function likedByTenants()
    {
        return $this->belongsToMany(User::class, config('tables.tenant_likes'), 'room_id', 'tenant_id')
            ->withTimestamps();
    }

    public function getIsSavedAttribute()
    {
        $user = auth()?->user();
        if (!$user) {
            return false; // not logged in
        }

        return $user->likedRooms()
            ->where('room_id', $this->attributes['id'])
            ->where('tenant_id', $user->id)
            ->exists();
    }

    // Accessor 
    public function getStatusAttribute()
    {
        return isset($this->attributes['status']) ? ($this->attributes['status'] == '1' ? 1 : 0) : 0;
    }

    // Mutator 
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower($value) == '1' ? 1 : 0;
    }

    public function filter($column, $value)
    {
        $answers = $this->relationLoaded('questionsanswer')
            ? $this->questionsanswer
            : $this->questionsanswer()->get();

        $ans = $answers->firstWhere($column, $value);

        if (!$ans) {
            return null;
        }
        return ($ans->answer ?? ($ans->option?->label_for_app ?? null));
    }

    public function getFirstImagePathAttribute()
    {
        $img = $this->images->first() ?? null;
        return $img?->path ? asset($img->path) : null;
    }

    public function getRoomTitleAttribute()
    {
        return $this->filter('question_id', 65);
    }

    public function boostUsages()
    {
        return $this->hasMany(RoomBoostUsage::class);
    }

    public function getIsBoostAppliedAttribute()
    {
        $lastUsage = $this->boostUsages()->latest('expires_at')->first();
        if (!$lastUsage) {
            return false;
        }
        return Carbon::parse($lastUsage->used_at)->addHours(24)->isFuture();
    }

    public function getBoostAppliedTimeAttribute()
    {
        $lastUsage = $this->boostUsages()->latest('expires_at')->first();
        return $lastUsage?->used_at;
    }

    public function getOwnerAttribute()
    {
        return $this->property?->propertyOwner?->user;
    }
}
