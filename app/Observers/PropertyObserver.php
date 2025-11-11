<?php

namespace App\Observers;

use App\Models\Property;
use App\Services\PlaceService;
use Illuminate\Support\Facades\Log;

class PropertyObserver
{
    /**
     * Handle the Property "created" event.
     */
    public function created(Property $property): void
    {
        Log::info("PropertyObserver: created fired for ID {$property->id}");
        $this->updateNearby($property);
    }

    /**
     * Handle the Property "updated" event.
     */
    public function updated(Property $property): void
    {
        Log::info("PropertyObserver: updated fired for ID {$property->id}");
        if ($property->isDirty(['latitude', 'longitude'])) {
            $this->updateNearby($property);
        }
    }

    /**
     * Handle the Property "deleted" event.
     */
    public function deleted(Property $property): void
    {
        //
    }

    /**
     * Handle the Property "restored" event.
     */
    public function restored(Property $property): void
    {
        //
    }

    /**
     * Handle the Property "force deleted" event.
     */
    public function forceDeleted(Property $property): void
    {
        //
    }

    protected function updateNearby(Property $property)
    {
        app(PlaceService::class)->saveNearbyForProperty(
            $property->latitude,
            $property->longitude,
            $property->id
        );
    }
}
