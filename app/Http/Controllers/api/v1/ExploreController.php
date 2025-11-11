<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\api\v1\BaseApiController;
use App\Http\Requests\ExploreRequest;
use App\Http\Resources\Custom\CustomRoomResource;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\DTOs\ExploreFiltersDTO;
use App\Models\Question;
use App\Services\ExploreRoomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Suburb;

class ExploreController extends BaseApiController
{

    /**
     * Display a roomsListing of the resource.
     */
    public function roomsListing(ExploreRequest $request)
    {
        $logInUserId = $request->user()->id;
        $authUser = User::whereId($logInUserId)->first(['id', 'email', 'latitude', 'longitude', 'role']);
        $perPage = $request->input("per_page", config('constants.per_page', 10));
        $isPaginate = filter_var($request->input('is_paginate', 'true'), FILTER_VALIDATE_BOOLEAN);

        $dto = new ExploreFiltersDTO(
            radius: $request->validated('radius'),
            latitude: $request->validated('latitude'),
            longitude: $request->validated('longitude'),
            authUser: $authUser,
            propertyId: $request->validated('property_id'),
            suburbIds: $request->validated('suburb_ids'),
            sortBy: $request->get('sortBy'),
            location: $request->validated('location'),
            isMap: $isPaginate,
            minRent: $request->validated('min_rent'),
            maxRent: $request->validated('max_rent'),
            billsIncluded: $request->validated('bills_included'),
            availability: $request->validated('availability'),
            minLengthOfStay: $request->validated('min_length_of_stay'),
            maxLengthOfStay: $request->validated('max_length_of_stay'),
            isFlexible: $request->validated('flexible'),
            housematePreferences: $request->validated('housemate_preferences'),
            accommodation: $request->validated('accommodation'),
            placesAccepting: $request->validated('places_accepting'),
            homeAccessibility: $request->validated('home_accessibility'),
            furnishings: $request->validated('furnishings'),
            bathroomType: $request->validated('bathroom_type'),
            numberOfHousematesOccupied: $request->validated('number_of_housemates_occupied'),
            parkingType: $request->validated('parking_type'),
            propertyFacilities: $request->validated('property_facilities'),
            subscriberFilters: $request->validated('subscriber_filters'),
        );

        $query = app(ExploreRoomService::class)->getListings($dto);

        if ($isPaginate) {
            $rooms = $query?->paginate($perPage);
            if ($rooms != null) {
                return ApiResponse::paginate($rooms, CustomRoomResource::collection($rooms));
            } else {
                return ApiResponse::success([]);
            }
        }

        $rooms = $query?->get();
        if ($query != null) {
            return ApiResponse::success(CustomRoomResource::collection($rooms));
        } else {
            return ApiResponse::success([]);
        }
    }

    public function savelisting(Request $request)
    {
        $authUser = $request->user();
        $userRole = $authUser->role ?? 1;
        $request->validate([
            'type' => 'nullable|string',
            'search' => 'nullable|string',
            'suburb_ids' => 'nullable|array',
            'suburb_ids.*' => 'integer|exists:' . Suburb::class . ',id',
        ]);

        $search = $request->input('search') ?? '';
        $suburbIds = $request->input('suburb_ids') ?? [];

        $type = $request->input('type') ?? 'room';

        $perPage = $request->input('per_page', config('constants.per_page', 10));
        $isPaginate = filter_var($request->input('is_paginate', true), FILTER_VALIDATE_BOOLEAN);

        $query = match ($userRole) {
            2 => $type === 'room'
                ? $authUser?->likedRooms()
                : $authUser?->likedUsers()->whereRole(2),
            3 => $authUser?->propertyOwner?->likedTenants()->whereRole(2),
            default => null,
        };

        if (!$query) {
            return ApiResponse::success([]);
        }

        $query->when($search, function ($q) use ($search) {
            // $q->where(function ($inner) use ($search) {
            //     $inner->where('first_name', 'LIKE', "%{$search}%")
            //         ->orWhere('last_name', 'LIKE', "%{$search}%");
            // });
            $q->whereHas('questionAnswers', fn($sQue) => $sQue->where('answer', 'LIKE', '%' . $search . '%')->where('question_id', 2));
        });

        $query->when(!empty($suburbIds), function ($q) use ($suburbIds) {
            $suburbs = Suburb::whereIn("id", $suburbIds)
                ->pluck('name')
                ->map(fn($s) => strtolower($s)) // lowercase for case-insensitive match
                ->toArray();

            $q->whereExists(function ($subQ) use ($suburbs) {
                $subQ->select(DB::raw(1))
                    ->from('question_answers_user as que')
                    ->whereColumn('que.property_id', 'rooms.property_id')
                    ->where('que.question_id', 57)
                    ->where(function ($subQ) use ($suburbs) {
                        foreach ($suburbs as $suburb) {
                            $subQ->orWhereRaw('LOWER(que.answer) LIKE ?', ["%{$suburb}%"]);
                        }
                    })
                    ->whereNull('que.deleted_at');
            });
        });

        $results = $isPaginate ? $query->paginate($perPage) : $query->get();

        $resourceClass = match (true) {
            $userRole == 2 && $type === 'room' => CustomRoomResource::class,
            default => UserResource::class,
        };

        $resource = $resourceClass::collection($results);

        return $isPaginate
            ? ApiResponse::paginate($results, $resource)
            : ApiResponse::success($resource);
    }

    public function filter(Request $request)
    {
        $questionIds = [75, 76, 77, 78, 56, 79, 62, 67, 68, 60, 63, 72];

        $questions = Question::whereNull('deleted_at')
            ->whereIn('id', $questionIds)
            ->with(['options' => function ($query) {
                $query->select('id', 'question_id', 'label_for_app', 'value')->orderBy('id');
            }])
            ->orderBy('id')
            ->get(['id', 'title_for_app', 'sub_title_for_app', 'type_for_app', 'question_for']);

        // Reorder collection to match the original order in $questionIds
        $questions = $questions->sortBy(function ($q) use ($questionIds) {
            return array_search($q->id, $questionIds);
        })->values();

        $data = QuestionResource::collection($questions);

        return $this->sendResponse($data, __('messages.fetche_success', ['item' => 'Explore Filter']));
    }
}
