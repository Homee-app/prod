<?php

namespace App\Services;

use App\DTOs\ExploreFiltersDTO;
use App\Models\PropertyOwner;
use App\Models\Room;
use App\Models\RoomBoostUsage;
use App\Models\Suburb;
use App\Traits\Common_trait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ExploreRoomService
{
    use Common_trait;

    public function getListings(ExploreFiltersDTO $filters)
    {
        $testing = false;
        $applyFilter = true; // for advanced filtering conditions
        try {
            $authUser = $filters->authUser;
            $authLatitude = $filters->latitude ?? ($authUser->latitude ?? 0);
            $authLongitude = $filters->longitude ?? ($authUser->longitude ?? 0);
            $radius = $filters->radius ?? 50;
            $suburbs = $this->getSuburbs($authUser, $filters);
            $ownerPropertyIds = PropertyOwner::whereUserId($authUser->id)?->first()?->properties()->pluck('id')->toArray() ?? [];

            $query = Room::query();

            $query->where('rooms.status', 1)
                ->whereNull('rooms.deleted_at')
                ->select('rooms.id', 'rooms.property_id', 'rooms.status', 'rooms.deleted_at', 'rooms.created_at','rooms.updated_at')
                ->join('properties as p', 'p.id', '=', 'rooms.property_id')
                ->with([
                    'property:id,owner_id,latitude,longitude,status,deleted_at,created_at',
                    'property.rooms:id,property_id,status,created_at',
                    'questionsanswer:id,question_id,option_id,answer,property_id,room_id',
                    'images',
                    'property.housemates.images',
                ]);

            // Exclude owner’s own properties
            if (!empty($ownerPropertyIds)) {
                $query->whereNotIn('rooms.property_id', $ownerPropertyIds);
            }

            // Surrounding suburb filter
            if (!$filters->isMap) {
                $query->whereRaw(
                    "(6371 * acos(cos(radians($authLatitude)) * cos(radians(p.latitude)) * cos(radians(p.longitude) - radians($authLongitude)) + sin(radians($authLatitude)) * sin(radians(p.latitude)))) <= $radius",
                );               
            }
            
            if ($applyFilter) {
                if ($filters->location) {
                    $query->whereExists(function ($q) use ($filters) {
                        $q->select(DB::raw(1))
                            ->from('question_answers_user as que')
                            ->whereColumn('que.room_id', 'rooms.id')
                            ->whereColumn('que.property_id', 'rooms.property_id')
                            ->where('que.question_id', 57)
                            ->where('que.answer', 'LIKE', "%{$filters->location}%")
                            ->whereNull('que.deleted_at');
                    });
                }
                if ($filters->accommodation) {
                    $query->whereExists(function ($q) use ($filters) {
                        $q->select(DB::raw(1))
                            ->from('question_answers_user as que')
                            ->whereColumn('que.property_id', 'p.id')
                            ->where('que.question_id', 56)
                            ->whereIn('que.option_id', $filters->accommodation)
                            ->whereNull('que.deleted_at');
                    });
                }
                if ($filters->homeAccessibility) {
                    $query->whereExists(function ($q) use ($filters) {
                        $q->select(DB::raw(1))
                            ->from('question_answers_user as que')
                            ->whereColumn('que.property_id', 'p.id')
                            ->where('que.question_id', 62)
                            ->whereIn('que.option_id', $filters->homeAccessibility)
                            ->whereNull('que.deleted_at');
                    });
                }
                if ($filters->propertyFacilities) {
                    $query->whereExists(function ($q) use ($filters) {
                        $q->select(DB::raw(1))
                            ->from('question_answers_user as que')
                            ->whereColumn('que.property_id', 'p.id')
                            ->where('que.question_id', 63)
                            ->whereIn('que.option_id', $filters->propertyFacilities)
                            ->whereNull('que.deleted_at');
                    });
                }
                if ($filters->parkingType) {
                    $query->whereExists(function ($q) use ($filters) {
                        $q->select(DB::raw(1))
                            ->from('question_answers_user as que')
                            ->whereColumn('que.property_id', 'p.id')
                            ->where('que.question_id', 60)
                            ->where('que.option_id', $filters->parkingType)
                            ->whereNull('que.deleted_at');
                    });
                }
                if (!empty($suburbs)) {
                    $query->whereExists(function ($q) use ($suburbs) {
                        $q->select(DB::raw(1))
                            ->from('question_answers_user as que')
                            ->whereColumn('que.property_id', 'p.id')
                            ->where('que.question_id', 57)
                            ->where(function ($subQ) use ($suburbs) {
                                foreach ($suburbs as $suburb) {
                                    $subQ->orWhereRaw('LOWER(que.answer) LIKE ?', ["%{$suburb}%"]);
                                }
                            })
                            ->whereNull('que.deleted_at');
                    });
                }
                if ($filters->propertyId) {
                    $query->where('p.id', $filters->propertyId);
                }

                if ($filters->subscriberFilters) {
                    foreach ($filters->subscriberFilters as $subscriberFilter) {
                        $type = $subscriberFilter['type'] ?? null;
                        $min  = $subscriberFilter['min'] ?? null;
                        $max  = $subscriberFilter['max'] ?? null;

                        if (!$type) continue;
                        Log::info("This is a type: $type, This is min value : $min, and this is a max value : $max");
                        if ($min != 0 || $max != 10) {
                            $query->whereExists(function ($q) use ($type, $min, $max) {
                                $q->select(DB::raw(1))
                                    ->from('nearby_places as np')
                                    ->whereColumn('np.property_id', 'p.id')
                                    ->where('np.type', $type);

                                if (!is_null($min) && $min != 0) {
                                    $q->whereRaw(
                                        "CAST(REPLACE(np.distance_text, ' km', '') AS DECIMAL(10,2)) >= ?",
                                        [$min]
                                    );
                                }

                                if (!is_null($max) && $max != 10) {
                                    $q->whereRaw(
                                        "CAST(REPLACE(np.distance_text, ' km', '') AS DECIMAL(10,2)) <= ?",
                                        [$max]
                                    );
                                }
                            });
                        }
                    }
                }

                if ($filters->minRent || $filters->maxRent) {
                    $query->whereExists(function ($q) use ($filters) {
                        $q->select(DB::raw(1))
                            ->from('question_answers_user as qau')
                            ->whereColumn('qau.room_id', 'rooms.id')
                            ->where('qau.question_id', 71);

                        if ($filters->minRent) {
                            $q->whereRaw('CAST(qau.answer AS UNSIGNED) >= ?', [$filters->minRent]);
                        }
                        if ($filters->maxRent) {
                            $q->whereRaw('CAST(qau.answer AS UNSIGNED) <= ?', [$filters->maxRent]);
                        }
                    });
                }
                if ($filters->billsIncluded) {
                    $query->whereExists(function ($q) use ($filters) {
                        $q->select(DB::raw(1))
                            ->from('question_answers_user as qau')
                            ->whereColumn('qau.room_id', 'rooms.id')
                            ->where('qau.question_id', 72)
                            ->where('qau.option_id', $filters->billsIncluded);
                    });
                }
                if ($filters->availability) {
                    $query->whereExists(function ($q) use ($filters) {
                        $q->select(DB::raw(1))
                            ->from('question_answers_user as qau')
                            ->whereColumn('qau.room_id', 'rooms.id')
                            ->where('qau.question_id', 75)
                            ->whereRaw(
                                "STR_TO_DATE(qau.answer, '%d/%m/%Y') = STR_TO_DATE(?, '%d/%m/%Y')",
                                [$filters->availability]
                            );
                    });
                }
                if (!$filters->isFlexible) {
                    // Handle Min Length of Stay
                    if (!empty($filters->minLengthOfStay)) {
                        $minOptions = $this->getOptionsIdValues(76);
                        $minLengthOfStay = $minOptions[$filters->minLengthOfStay] ?? null;

                        if ($minLengthOfStay) {
                            $query->whereExists(function ($q) use ($minLengthOfStay) {
                                $q->select(DB::raw(1))
                                    ->from('question_answers_user as qau')
                                    ->whereColumn('qau.room_id', 'rooms.id')
                                    ->where('qau.question_id', 76)
                                    ->where('qau.option_id', '>=', $minLengthOfStay);
                            });
                        }
                    }

                    // Handle Max Length of Stay
                    if (!empty($filters->maxLengthOfStay)) {
                        $maxOptions = $this->getOptionsIdValues(77);
                        $maxLengthOfStay = $maxOptions[$filters->maxLengthOfStay] ?? null;

                        if ($maxLengthOfStay) {
                            $query->whereExists(function ($q) use ($maxLengthOfStay) {
                                $q->select(DB::raw(1))
                                    ->from('question_answers_user as qau')
                                    ->whereColumn('qau.room_id', 'rooms.id')
                                    ->where('qau.question_id', 77)
                                    ->where('qau.option_id', '<=', $maxLengthOfStay);
                            });
                        }
                    }
                }

                if ($filters->housematePreferences) {
                    $query->whereExists(function ($q) use ($filters) {
                        $q->select(DB::raw(1))
                            ->from('question_answers_user as qau')
                            ->whereColumn('qau.room_id', 'rooms.id')
                            ->where('qau.question_id', 78)
                            ->where('qau.option_id', $filters->housematePreferences);
                    });
                }
                if ($filters->placesAccepting) {
                    $query->whereExists(function ($q) use ($filters) {
                        $q->select(DB::raw(1))
                            ->from('question_answers_user as qau')
                            ->whereColumn('qau.room_id', 'rooms.id')
                            ->where('qau.question_id', 79)
                            ->whereIn('qau.option_id', $filters->placesAccepting);
                    });
                }
                if ($filters->furnishings) {
                    $query->whereExists(function ($q) use ($filters) {
                        $q->select(DB::raw(1))
                            ->from('question_answers_user as qau')
                            ->whereColumn('qau.room_id', 'rooms.id')
                            ->where('qau.question_id', 67)
                            ->where('qau.option_id', $filters->furnishings);
                    });
                }
                if ($filters->bathroomType) {
                    $query->whereExists(function ($q) use ($filters) {
                        $q->select(DB::raw(1))
                            ->from('question_answers_user as qau')
                            ->whereColumn('qau.room_id', 'rooms.id')
                            ->where('qau.question_id', 68)
                            ->where('qau.option_id', $filters->bathroomType);
                    });
                }
            }

            // Add dynamic conditions
            if (!empty($filters->numberOfHousematesOccupied) && $filters->numberOfHousematesOccupied > 0) {
                $query->leftJoin('housemates as hm', function ($join) {
                    $join->on('hm.property_id', '=', 'p.id')->whereNull('hm.deleted_at');
                });
                $query->groupBy('rooms.id')->havingRaw('COUNT(DISTINCT hm.id) >= ?', [(int) $filters->numberOfHousematesOccupied]);
            } else {
                $query->groupBy('rooms.id');
            }

            $sortCases = [
                1 => "CAST(COALESCE((SELECT answer FROM question_answers_user WHERE question_id = 71 AND room_id = rooms.id LIMIT 1), '0') AS UNSIGNED) DESC",
                2 => "CAST(COALESCE((SELECT answer FROM question_answers_user WHERE question_id = 71 AND room_id = rooms.id LIMIT 1), '0') AS UNSIGNED) ASC",
                3 => "STR_TO_DATE((SELECT answer FROM question_answers_user WHERE question_id = 75 AND room_id = rooms.id ORDER BY answer DESC LIMIT 1), '%d/%m/%Y') DESC",
                4 => "STR_TO_DATE((SELECT answer FROM question_answers_user WHERE question_id = 75 AND room_id = rooms.id ORDER BY answer DESC LIMIT 1), '%d/%m/%Y') ASC",
            ];

            $query->distinct()->orderByRaw($sortCases[$filters->sortBy] ?? 'rooms.id DESC');

            if ($testing) {
                Log::info("This is a query for export room services : " . $query);
            }

            Log::info(date('Y-m-d H:i:s'), ["query : " => $query->toSql()]);
            return $query;
        } catch (\Exception $e) {
            $error = $e->getMessage() . ' - ' . $e->getFile() . '' . $e->getLine();
            Log::error($error);
        }
    }

    public function getSuburbs($authUser, $filters)
    {
        $suburbQuery = Suburb::where('country', 'Australia');

        if ($filters->suburbIds) {
            $suburbQuery->whereIn("id", $filters->suburbIds);
        } else {
            $suburbIds = $authUser->suburbs()->pluck('option_id')->toArray();
            $suburbQuery->whereIn("id", $suburbIds);
        }

        return $suburbQuery->pluck('name')
            ->map(fn($s) => strtolower($s))
            ->toArray();
    }
}
