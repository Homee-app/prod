<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\api\v1\BaseApiController;
use App\Http\Resources\PropertyResouce;
use App\Http\Resources\Custom\CustomPropertyResource;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\QuestionAnswerUser;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Constants\QuestionConstants;
use App\Services\Notifications\NotificationService;
use App\Services\PlaceService;

class PropertyController extends BaseApiController
{
    use Common_trait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->role != 3) {
            return ApiResponse::error(__('messages.unauthorized'), 403);
        }
        $perPage = $request->input("per_page", config('constants.per_page', 10));
        $isPaginate = filter_var($request->input('is_paginate', 'true'), FILTER_VALIDATE_BOOLEAN);
        $propertyOwner = PropertyOwner::where("user_id", $user->id)->first();
        if (!empty($propertyOwner)) {
            $query = Property::with([
                'rooms:id,status,property_id,created_at',
                'questionsanswer.option',
                'rooms.images',
                'nearbyPlaces',
            ])
                ->whereOwnerId($propertyOwner->id);

            if ($isPaginate) {
                $properties = $query->paginate($perPage);
                return ApiResponse::paginate($properties, CustomPropertyResource::collection($properties));
            }

            $properties = $query->get();
            return ApiResponse::success(CustomPropertyResource::collection($properties));
        } else {
            return response()->json(['data' => [], 'message' => __('messages.not_found', ['item' => 'Property'])]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'property_id' => 'nullable|exists:' . Property::class . ',id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'owner_lives_here' => 'nullable|boolean',

            'answers' => 'required|array',
            'answers.*.question_id' => 'nullable|exists:questions,id',
            'answers.*.option_id' => 'nullable',
            'answers.*.answer' => 'nullable|string',
            'answers.*.for_partner' => 'nullable|boolean',
            'answers.*.file' => 'nullable|file',

            // Optional nested housemate answers inside an answer
            'answers.*.housemates' => 'nullable|array',
            'answers.*.housemates.*.question_id' => 'required|exists:questions,id',
            'answers.*.housemates.*.option_id' => 'nullable',
            'answers.*.housemates.*.answer' => 'nullable|string',
            'answers.*.housemates.*.file' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
        ]);

        if ($user->role != 3) {
            return ApiResponse::error(__('messages.unauthorized'), 401);
        }

        $answers = $request->answers;
        // if( $request->has('owner_lives_here') && ($request->owner_lives_here == '1' || $request->owner_lives_here == true)){
        //     $answers[] = [
        //         "question_id" => "64",
        //         "option_id" => "126",
        //         "answer" => null,
        //     ];
        // }else{
        //     $answers[] = [
        //         "question_id" => "64",
        //         "option_id" => "123",
        //         "answer" => null,
        //     ];
        // }
        $questionIds = collect($answers)->pluck('question_id')->unique();
        DB::beginTransaction();
        try {
            $createPropertyOwener = PropertyOwner::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'user_id' => $user->id,
            ]);
            $property = (object) [];
            if ($request->property_id) {
                $property = Property::whereStatus('1')->find($request->property_id);
                if (!$property?->id) {
                    return ApiResponse::notFound(__('messages.not_found', ['item' => 'Property']));
                }
                if ($request->has('latitude')) {
                    $property->update([
                        'latitude' => $request->latitude,
                    ]);
                }
                if ($request->has('longitude')) {
                    $property->update([
                        'longitude' => $request->longitude,
                    ]);
                }
                QuestionAnswerUser::where('user_id', $user->id)
                    ->whereIn('question_id', $questionIds)
                    ->where('property_id', $property?->id)
                    ->where(function ($q) {
                        $q->whereNull('room_id')
                            ->orWhereNull('housemate_id');
                    })
                    ->forceDelete();
            } else {
                $property = $createPropertyOwener->properties()->create(
                    [
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                        'status' => true,
                    ]
                );
                app(PlaceService::class)->saveNearbyForProperty(
                    $property->latitude,
                    $property->longitude,
                    $property->id
                );
            }
            $propertyId = $property->id;
            $propertyData = $this->makeQueAns($answers, $user, $propertyId);
            if (!empty($propertyData)) {
                // insert property data
                QuestionAnswerUser::insert($propertyData);
            }

            if ($questionIds->contains(56) && $request->has('owner_lives_here')) {
                $property->update([
                    'owner_id' => $createPropertyOwener->id,
                ]);
            }

            DB::commit();
            return ApiResponse::success(['id' => $property->id], __('messages.success_msg', ['item' => 'Property ' . ($request->property_id ? 'updated' : 'created')]));
        } catch (\Exception $e) {
            DB::rollBack();
            $error = $e->getMessage() . ' - ' . $e->getFile();
            Log::error($error);
            return ApiResponse::notFound(__('messages.something_error'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $property = Property::with([
            'rooms:id,status,property_id,created_at',
            'questionsanswer.option',
            'rooms.images',
            'nearbyPlaces'
        ])->find($id);
        if (!$property?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Property']));
        }
        return ApiResponse::success(new PropertyResouce($property));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role != 3) {
            return ApiResponse::error(__('messages.unauthorized'), 401);
        }

        $property = Property::find($id);
        if (!$property?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Property']));
        }

        try {
            // if($property->status === 1){
            //     return ApiResponse::notFound(__('messages.not_found',['item' => 'Inactive Property']));
            // }

            foreach ($property->rooms as $room) {
                $this->deleteAllImages($room->images);
            }

            // 3. Delete housemate images
            foreach ($property->housemates as $housemate) {
                $this->deleteAllImages($housemate->images);
            }

            // Notification::where

            QuestionAnswerUser::wherePropertyId($id)->forceDelete();
            $property?->housemates()?->forceDelete();
            $property?->rooms()?->forceDelete();
            $property->forceDelete();
            return ApiResponse::success([], __('messages.success_msg', ['item' => 'Property deleted']));
        } catch (\Exception $e) {
            $error = $e->getMessage() . ' - ' . $e->getFile() . '' . $e->getLine();
            Log::error($error);
            return ApiResponse::notFound(__('messages.something_error'));
        }
    }

    public function statusUpdate(Request $request, $propertyId)
    {
        $curreantUser = request()->user();
        if ($curreantUser->role != 3) {
            return ApiResponse::error(__('messages.unauthorized'), 401);
        }

        if (!$curreantUser->can_add_property && $request->status == 1) {
            return ApiResponse::error(__('messages.enable_property'), 400);
        }

        $property = Property::with(['property_owner.user'])->find($propertyId);
        $statusArray = [
            '0' => 'Deactivated',
            '1' => 'Activated',
        ];
        if (!$property?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Property']));
        }
        $request->validate([
            'status' => 'required|boolean',
        ]);
        if ($request->status == '0') {
            $property->rooms()?->update([
                'status' => $request->status,
            ]);
            $property->rooms()->chunk(2, function ($rooms) {
                foreach ($rooms as $room) {
                    $roomTitle = QuestionAnswerUser::whereRoomId($room->id)->whereQuestionId(65)->value('answer');
                    app(NotificationService::class)->send(
                        'room_status_update',
                        $room->id,
                        $roomTitle,
                        $room->first_image,
                        null,
                        [],
                        null
                    );
                }
            });
        }
        $property->status = $request->status;
        $property->save();
        return ApiResponse::success(new PropertyResouce($property), __('messages.success_msg', ['item' => 'Property ' . $statusArray[$request->status]]));
    }

    public function getGroupedByDetails(Request $request, $propertyId)
    {
        $user = $request->user();
        $property = Property::with(['property_owner.user', 'questionsanswer'])->find($propertyId);

        if (!$property?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Property']));
        }

        $dataArray = $this->makeQuestionsGroupBy($user, $propertyId);

        $data = [
            'id' => $property->id,
            'status' => $property->status == '1' ? 1 : 0,
            'accommodation' => $property->filter('question_id', 56),
            'address' => $property->filter('question_id', 57),
            'bedrooms_count' => $property->filter('question_id', 58),
            'bathrooms_count' => $property->filter('question_id', 59),
            'housemates_count' => $property->housemates_count,
        ];
        $handledScreens = [];
        $details = [];
        foreach ($dataArray as $screen) {
            $screenId = $screen['screen_id'];
            $questions = $screen['questions'];
            $handledScreens[] = $screenId;
            switch ($screenId) {
                case 17: // Describe Your Place
                    $question = $questions[0];
                    $selectedOptionId = $question->userAnswer->first()?->option_id;
                    $selected = $question->options->firstWhere('id', $selectedOptionId);
                    $value = $selected?->label_for_app ?? '';
                    $details[$screenId] = [
                        'screen_id' => $screenId,
                        'section' => 'Describe Your Place',
                        'value' => $value,
                        'is_competed' => $value === '' ? false : true
                    ];
                    break;
                case 18: // About the Property
                    $value = makeAnswerSting($questions);
                    $details[$screenId] = [
                        'screen_id' => $screenId,
                        'section' => 'About the Property',
                        'value' => $value,
                        'is_competed' => $value === '' ? false : true
                    ];
                    break;
                case 19: // About the Homees
                    $count = $property->housemates_count ?? 0;
                    $value = checkString($questions[0]->id, $count);
                    $details[$screenId] = [
                        'screen_id' => $screenId,
                        'section' => 'About the Homees',
                        'value' => $value,
                        'is_competed' => $value === '' ? false : true
                    ];
                    break;
                // case 20:
                //     $value = null;
                //    $details[$screenId] = [
                //         'screen_id' => $screenId,
                //         'section' => '',
                //         'value' => $value,
                //         'is_competed' => $value === '' ? false : true
                //     ];
                //     break;
                case 21: // About You and Your Property
                    $value = makeAnswerSting($questions);
                    $details[$screenId] = [
                        'screen_id' => $screenId,
                        'section' => 'About You and Your Property',
                        'value' => $value,
                        'is_competed' => $value === '' ? false : true
                    ];
                    break;
            }
        }

        if (!in_array(19, $handledScreens)) {
            $count = $property->housemates_count ?? 0;
            $details[19] = [
                'screen_id' => 19,
                'section' => 'About the Homees',
                'value' => $count . ' Homees',
                'is_competed' => false
            ];
        }
        $data['details'] = [];
        $orderedScreens = QuestionConstants::SESSION_QUESTIONS_FOR['property'];
        foreach ($orderedScreens as $screenId) {
            if (isset($details[$screenId])) {
                $data['details'][] = $details[$screenId];
            }
        }

        $data['propery_status'] = $user->can_add_property;
        $data['room_status'] = $user->can_add_room;
        
        return ApiResponse::success($data);
    }

    public function saveNearbyForProperty(Request $request)
    {
        $id = $request->input('id');
        $property = Property::find($id);

        if ($property) {
            $return =  app(PlaceService::class)->saveNearbyForProperty($property->latitude, $property->longitude, $id);
            if ($return) {
                return ApiResponse::success([], __('messages.success_msg', ['item' => 'Nearby place saved for Property']));
            } else {
                return ApiResponse::error(__('messages.something_went_wrong'), 422);
            }
        } else {
            return ApiResponse::error(__('messages.something_went_wrong'), 422);
        }
    }
}
