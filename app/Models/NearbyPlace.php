<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NearbyPlace extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.nearby_places');
       
    }

    protected $fillable = [
        'property_id','type','distance_text','duration_text'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    const TYPES = [
            'Bus stop'          => 'bus_station',
            'Train station'     => 'train_station',
            'Grocery store'     => 'grocery_or_supermarket',
            'Gym'               => 'gym',
            // 'Gas station'       => 'gas_station',
            // 'Hospital'          => 'hospital',
            // 'Pharmacy'          => 'pharmacy',
            // 'School'            => 'school',
            // 'University'        => 'university',
            // 'Restaurant'        => 'restaurant',
            // 'Cafe'              => 'cafe',
            // 'Shopping mall'     => 'shopping_mall',
            // 'Park'              => 'park',
            // 'Bank'              => 'bank',
            // 'ATM'               => 'atm',
            // 'Airport'           => 'airport',
            // 'Police station'    => 'police',
            // 'Post office'       => 'post_office',
            // 'Library'           => 'library',
            // 'Movie theater'     => 'movie_theater',
    ];

    const TYPE_ICONS = [
        'Bus stop' => 'images/options/bus_station.svg',
        'Train station' => 'images/options/train.svg',
        'Grocery store' => 'images/options/fastfood.svg',
        'Gym' => 'images/options/Gym.svg',
        'Gas station' => 'images/options/car.svg',
    ];

}
