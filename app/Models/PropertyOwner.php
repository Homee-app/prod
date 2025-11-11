<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyOwner extends Model
{
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.property_owners');
       
    }

    protected $fillable  = [
        'user_id',
        'living_situation'
    ];

    
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function properties(){
        return $this->hasMany(Property::class, 'owner_id','id');
    }

    public function likedTenants()
    {
        return $this->belongsToMany(User::class, config('tables.owner_likes'),  'owner_id','tenant_id')
                    ->withTimestamps();
    }
}   
