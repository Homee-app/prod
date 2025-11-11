<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
     use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.properties');
       
    }

    protected $fillable = [
        'owner_id','latitude','longitude','status','owner_lives_here'
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function roommates(): HasMany
    {
        return $this->hasMany(Roommate::class);
    }

    public function questionsanswer(): HasMany{
        return $this->hasMany(QuestionAnswerUser::class,'property_id','id')->whereNull('housemate_id')->whereNull('room_id');
    }

    public function property_owner(): BelongsTo{
        return $this->belongsTo(PropertyOwner::class,'owner_id');
    }

    public function getRouteKeyName(): String
    {
        return 'id';
    }
    
    public function housemates(): HasMany{
        return $this->hasMany(Housemate::class,'property_id');
    }

    public function getRoomImageAttribute()
    {
        $firstRoom = $this->rooms()->with('images')->first();

        return $firstRoom?->images->first()?->path ?? null;
    }

    public function getRoomRentAttribute()
    {
        $firstRoom = $this->rooms()->with(['questionsanswer' => function ($q) {
            $q->where('question_id', 71);
        }])->first();

        return $firstRoom?->questionsanswer->first()?->answer ?? 0;
    }

    public function getRoomsCountAttribute()
    {
        $countRooms = $this->rooms()->count('id');
        return $countRooms ?? 0;
    }

    public function getHousematesCountAttribute()
    {
        $countHousemates = $this->housemates()->count('id');
        return $countHousemates ?? 0;
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

    public function filter($column, $value){
        $answers = $this->relationLoaded('questionsanswer')
            ? $this->questionsanswer
            : $this->questionsanswer()->get();

        $ans = $answers->firstWhere($column, $value);

        if (!$ans) {
            return null;
        }

        return $ans->answer ?? $ans->option?->label_for_app ?? null;
    }

    // Remove this:
    protected $with = ['rooms'];

    public function getHousemateImagesAttribute()
    {
        return $this->housemates->flatMap->images;
    }

    public function nearbyPlaces()
    {
        return $this->hasMany(NearbyPlace::class);
    }
}
