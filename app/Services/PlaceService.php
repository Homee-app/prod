<?php

namespace App\Services;

use App\Models\NearbyPlace;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlaceService
{
    protected $googleKey;

    public function __construct()
    {
        $this->googleKey = config('services.google.places_key');
    }

    public function fetchNearbyDetails(float $lat, float $lng): array
    {
        $types = NearbyPlace::TYPES;
        $places = [];
        foreach ($types as $label => $type) {
            $result = $this->getNearestPlace($lat, $lng, $type, $label);

            Log::info('NearbyPlace:');
            Log::info($result);

            if ($result) {
                $places[] = $result;
            }
        }

        return $places;
    }

    private function getNearestPlace(float $lat, float $lng, string $type, string $label): ?array
    {
        sleep(1);
        try {
            // Google Places Nearby Search API
            $nearbyUrl = "https://maps.googleapis.com/maps/api/place/nearbysearch/json";
            $nearbyRes = Http::withoutVerifying()->get($nearbyUrl, [
                'location' => "$lat,$lng",
                'radius' => 10000, // 10 Km
                'type' => $type,
                'key' => $this->googleKey,
            ])->json();

            Log::info('Nearby places response:');
            Log::info($nearbyRes);

            if (!empty($nearbyRes['results'])) {
                $firstPlace = $nearbyRes['results'][0];
                $placeLat = $firstPlace['geometry']['location']['lat'];
                $placeLng = $firstPlace['geometry']['location']['lng'];

                // Google Distance Matrix API
                $matrixUrl = "https://maps.googleapis.com/maps/api/distancematrix/json";
                $matrixRes = Http::withoutVerifying()->get($matrixUrl, [
                    'origins' => "$lat,$lng",
                    'destinations' => "$placeLat,$placeLng",
                    'mode' => 'walking', // change to driving if needed
                    'key' => $this->googleKey,
                ])->json();

                Log::info('This is a google key : ' . $this->googleKey);
                Log::info("$lat,$lng");
                
                Log::info('results:');

                Log::info($matrixRes);

                if (
                    isset($matrixRes['rows'][0]['elements'][0]['status']) && $matrixRes['rows'][0]['elements'][0]['status'] === 'OK'
                ) {
                    $element = $matrixRes['rows'][0]['elements'][0];
                    Log::info('element-data:');
                    Log::info($element);
                    return [
                        'type' => $label,
                        'distance' => $element['distance']['text'],
                        'duration' => $element['duration']['text'],
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Error fetching $label: " . $e->getMessage() . ' - ' . $e->getLine() . ' - ' . $e->getFile());
        }

        return null;
    }

    public function saveNearbyForProperty($lat = null, $lng = null, $id = null)
    {
        if ($lat !== null && $lng !== null && $id !== null) {
            $places = $this->fetchNearbyDetails($lat, $lng);

            Log::info('Nearby places data:', $places);
            if (empty($places)) {
                return false;
            }
            foreach ($places as $place) {
                NearbyPlace::updateOrCreate(
                    [
                        'property_id' => $id,
                        'type' => $place['type']
                    ],
                    [
                        'distance_text' => $place['distance'],
                        'duration_text' => $place['duration'],
                    ]
                );
            }
            return true;
        }
        return false;
    }
}
