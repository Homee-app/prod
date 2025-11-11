<?php

namespace App\Http\Controllers\api\v1;

use App\Constants\QuestionConstants;
use App\Helpers\ApiResponse;
use App\Http\Controllers\api\v1\BaseApiController;
use App\Http\Resources\ImageResouce;
use App\Http\Resources\RoomResouce;
use App\Models\Property;
use App\Models\Question;
use App\Models\QuestionAnswerUser;
use App\Models\Room;
use App\Services\Notifications\NotificationService;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RoomController extends BaseApiController
{
    use Common_trait;

    /**
     * Display a listing of the resource.
     */
    public function index($propertyId, Request $request)
    {
        $property = Property::find($propertyId);
        $perPage = $request->input("per_page", config('constants.per_page', 10));
        $isPaginate = filter_var($request->input('is_paginate', 'true'), FILTER_VALIDATE_BOOLEAN);

        if (!$property?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Property']));
        }

        $query = Room::with(['images'])->wherePropertyId($propertyId);

        if ($isPaginate) {
            $rooms = $query->paginate($perPage);
            return ApiResponse::paginate($rooms, RoomResouce::collection($rooms));
        }

        $rooms = $query->get();
        return ApiResponse::success(RoomResouce::collection($rooms));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store($propertyId, Request $request)
    {
        $user = $request->user();
        $rules = [
            'room_id' => 'nullable|exists:' . Room::class . ',id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:' . Question::class . ',id',
            'answers.*.option_id' => 'nullable',
            'answers.*.answer' => 'nullable|string',
        ];

        if ($request->filled('room_id')) {
            $rules['answers.*.file'] = 'nullable|array|max:11';
        } else {
            $rules['answers.*.file'] = 'nullable|array|max:11';
            $rules['answers.*.file.*'] = 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:51200'; // base64 or saved paths // 50 mb  
        }

        $request->validate($rules);

        // if($user->role != 3){
        //     return ApiResponse::error(__('messages.unauthorized'), 401);
        // }

        $property = Property::whereStatus('1')->find($propertyId);
        if (!$property?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Property']));
        }

        $answers = $request->answers;
        $roomId = $request->room_id ?? null;
        $questionAnswerData = $imagePaths = [];
        $propertyStatus = $property->status == '1' ? 1 : 0;
        if ($roomId) {
            $questionIds = collect($request->answers)->pluck('question_id')->unique();
            $room = Room::where('property_id', $propertyId)->whereStatus('1')->find($roomId);
            if (!$room?->id) {
                return ApiResponse::notFound(__('messages.not_found', ['item' => 'Room']));
            }
            QuestionAnswerUser::where('user_id', $user->id)
                ->whereIn('question_id', $questionIds)
                ->where('property_id', $propertyId)
                ->where('room_id', $roomId)
                ->forceDelete();
        } else {
            $room = $property->rooms()->create([
                'property_id' => $propertyId,
                'status' => $propertyStatus,
            ]);
        }
        DB::beginTransaction();
        try {
            $roomId = $room->id;
            foreach ($answers as $roomAnswers) {
                $question = Question::find($roomAnswers['question_id']);
                // Handle file upload for question 86
                if ($question->id === 86 && $roomId && !empty($roomAnswers['file'])) {
                    $files = $roomAnswers['file'];
                    foreach ($files as $file) {
                        $uploadBasePath = 'property/' . $propertyId . '/rooms/' . $roomId;
                        $filePath = $this->file_upload($file, $uploadBasePath);
                        Log::info('This is a filePath data : ');
                        Log::info($filePath);
                        $imagePaths[] = [
                            'path' => $filePath['original'],
                            'type' => $filePath['type'] === 'image' ? 0 : 1,
                            'thumbnail_path' => $filePath['thumbnail']
                        ];
                    }
                    $room->images()->createMany($imagePaths);
                }
                // if(!empty($roomAnswers['option_id']) || !empty($roomAnswers['answer'])){
                $data = $this->manageAllQueAns($roomAnswers, $question, $user, $propertyId, null, $roomId);
                $questionAnswerData = array_merge($questionAnswerData, $data);
                // }
            }
            if (!empty($questionAnswerData)) {
                QuestionAnswerUser::insert($questionAnswerData);
                DB::commit();
                if ($request->room_id) {
                    $roomTitle = Cache::remember("room_title_{$request->room_id}",  now()->addMinutes(1), function () use ($request, $questionAnswerData) {
                        return collect($questionAnswerData)
                            ->where('question_id', 65)
                            ->pluck('answer')
                            ->first()
                            ?? QuestionAnswerUser::whereRoomId($request->room_id)
                            ->whereQuestionId(65)
                            ->value('answer');
                    });
                    $notifyType = 'room_detail_update';
                    foreach ($questionAnswerData as $queId) {
                        if (in_array($queId['question_id'], [71, 72, 73, 74])) {
                            $notifyType = 'room_price_update';
                            break;
                        }
                    }
                    app(NotificationService::class)->send(
                        $notifyType,
                        $request->room_id,
                        $roomTitle,
                        $room->first_image_path,
                        null,
                        [],
                        null
                    );
                }
            }
            // DB::rollBack();
            DB::commit();
            $newRoom = $room->with([
                'property.housemates.images',
                'property.questionsanswer.question',
                'images',
                'questionsanswer.question',
                'property.property_owner.user',
                'property.nearbyPlaces'
            ])->find($roomId);
            return ApiResponse::success(new RoomResouce($newRoom), __('messages.success_msg', ['item' => 'Room ' . ($request->room_id ? 'updated' : 'created')]));
            // return ApiResponse::success(new RoomResouce($room),__('messages.success_msg',['item'=> 'Room created']));
        } catch (\Exception $e) {
            DB::rollBack();
            $error = $e->getMessage() . ' - ' . $e->getFile() . ' - ' . $e->getLine();
            Log::error($error);
            return ApiResponse::notFound(__('messages.something_error'));
        }
    }

    /**
     * Display the specified resource.
     */


    public function show(Request $request, $propertyId, $id)
    {
        $currentUser = $request->user();
        $property = Property::find($propertyId);

        // Check if property exists
        if (!$property) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Property']));
        }

        if ($property->status != 1) {
            return ApiResponse::notFound("Your saved room property is no longer available.");
        }

        $room = $property->rooms()->with([
            'property.housemates.images',
            'property.questionsanswer.question',
            'images',
            'questionsanswer.question',
            'property.property_owner.user',
            'property.nearbyPlaces'
        ])->find($id);

        // Check if room exists
        if (!$room) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Room']));
        }

        if ($currentUser->role == 2) {
            if ($room->status != 1) {
                return ApiResponse::notFound("Your saved room is no longer available.");
            }
        }

        return ApiResponse::success(new RoomResouce($room));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $propertyId, $id)
    {
        $user = $request->user();
        if ($user->role != 3) {
            return ApiResponse::error(__('messages.unauthorized'), 401);
        }

        $property = Property::find($propertyId);
        if (!$property?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Property']));
        }
        $room = $property->rooms()?->find($id);
        if (!$room?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Room']));
        }
        // if($room->status === 1){
        //     return ApiResponse::notFound(__('messages.not_found',['item' => 'Inactive Room']));
        // }

        foreach ($room->images as $image) {
            if (file_exists(public_path(($image->path)))) {
                unlink(public_path($image->path));
            }
            $image->forceDelete();
        }

        QuestionAnswerUser::whereRoomId($id)->delete();
        $room->forceDelete();
        return ApiResponse::success([], __('messages.success_msg', ['item' => 'Room deleted']));
    }

    public function statusUpdate(Request $request, $propertyId, $roomId)
    {
        $user = $request->user();
        if ($user->role != 3) {
            return ApiResponse::error(__('messages.unauthorized'), 401);
        }

        if(!$user->can_add_room && $request->status == 1){
            return ApiResponse::error(__('messages.enable_room'), 400);
        }
        
        $property = Property::find($propertyId);

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

        $room = $property->rooms?->find($roomId);

        if (!$room?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Room']));
        }

        $room->status = $request->status;
        $roomTitle = Cache::remember("room_title_{$room->id}",  now()->addMinutes(config('cache.default_time')), function () use ($room) {
            return QuestionAnswerUser::whereRoomId($room->id)->whereQuestionId(65)->value('answer');
        });
        $room->save();

        if ($request->status == 0) {
            app(NotificationService::class)->send(
                'room_status_update',
                $roomId,
                $roomTitle,
                $room->first_image,
                null,
                [],
                null
            );
        }

        return ApiResponse::success(new RoomResouce($room), __('messages.success_msg', ['item' => 'Room ' . $statusArray[$request->status]]));
    }

    public function getGroupedByDetails(Request $request, $propertyId, $roomId)
    {
        $user = $request->user();
        $property = Property::with(['property_owner.user'])->find($propertyId);

        if (!$property?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Property']));
        }

        $room = Room::find($roomId);
        if (!$room?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Room']));
        }

        $dataArray = $this->makeQuestionsGroupBy($user, $propertyId, $roomId);

        $data = [
            'id' => $room->id,
            'status' => $room->status == '1' ? 1 : 0,
            'weekly_rent' => $room->filter('question_id', 71),
            'image' => $room->first_image_path,
        ];
        $details = [];
        foreach ($dataArray as $screen) {
            $screenId = $screen['screen_id'];
            $questions = $screen['questions'];
            switch ($screenId) {
                case 22: // 
                    $value = makeAnswerSting($questions);
                    $details[$screenId] = [
                        'screen_id' => $screenId,
                        'section' => 'About the Room',
                        'value' => $value,
                        'is_competed' => $value === '' ? false : true
                    ];
                    break;
                case 23: // 
                    $value = makeAnswerSting($questions);
                    $details[$screenId] = [
                        'screen_id' => $screenId,
                        'section' => 'Room Features',
                        'value' => $value,
                        'is_competed' => $value === '' ? false : true
                    ];
                    break;
                case 24: // 
                    $value = makeAnswerSting($questions);
                    $details[$screenId] = [
                        'screen_id' => $screenId,
                        'section' => 'Rent, Bond and Bills',
                        'value' => $value,
                        'is_competed' => $value === '' ? false : true
                    ];
                    break;
                case 25: //  
                    $value = makeAnswerSting($questions);
                    $details[$screenId] = [
                        'screen_id' => $screenId,
                        'section' => 'Room Availability',
                        'value' => $value,
                        'is_competed' => $value === '' ? false : true
                    ];
                    break;
                case 27: // 
                    $value = makeAnswerSting($questions);
                    $details[$screenId] = [
                        'screen_id' => $screenId,
                        'section' => 'Homee Preferences',
                        'value' => $value,
                        'is_competed' => $value === '' ? false : true
                    ];
                    break;
            }
        }

        $room_Images = $property->room_image ? true : false;

        if (count($details) > 0) {
            $details[26] = [
                'screen_id' => 26,
                'section' => 'Property and Room Images',
                'value' => "",
                'is_competed' => $room_Images === '' ? false : true
            ];
        }

        $data['details'] = [];
        $orderedScreens = QuestionConstants::SESSION_QUESTIONS_FOR['room'];
        foreach ($orderedScreens as $screenId) {
            if (isset($details[$screenId])) {
                $data['details'][] = $details[$screenId];
            }
        }
        return ApiResponse::success($data);
    }

    public function getRoomImages(Request $request, $propertyId, $id)
    {
        $perPage = $request->input("per_page", config('constants.per_page', 10));
        $isPaginate = filter_var($request->input('is_paginate', 'false'), FILTER_VALIDATE_BOOLEAN);
        $property = Property::find($propertyId);
        if (!$property?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Property']));
        }
        $room = $property->rooms()?->find($id);
        if (!$room?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Room']));
        }
        $query = $room->images();
        if ($isPaginate) {
            $images = $query->paginate($perPage);
            return ApiResponse::paginate($images, ImageResouce::collection($images));
        }
        $images = $query->get();
        return ApiResponse::success(ImageResouce::collection($images));
    }

    public function toggleLike(Request $request)
    {
        $tenant = $request->user();
        if ($tenant->role != 2) {
            return ApiResponse::error(__('messages.unauthorized'),  401);
        }

        $validator = Validator::make($request->all(), [
            'room_id' => 'required',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $room = Room::find($request->room_id);
        if (!$room?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Room']));
        }

        if ($tenant->likedRooms()->where('room_id', $room->id)->exists()) {
            $tenant->likedRooms()->detach($room->id); // remove like
            return ApiResponse::success(__('messages.success_msg', ['item' => 'Room removed']));
        }

        $tenant->likedRooms()->attach($room->id); // add like
        return ApiResponse::success(__('messages.success_msg', ['item' => 'Room added']));
    }
}
