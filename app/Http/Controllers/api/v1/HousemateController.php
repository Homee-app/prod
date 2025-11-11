<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\api\v1\BaseApiController;
use App\Http\Resources\HousemateResource;
use App\Models\Housemate;
use App\Models\Property;
use App\Models\Question;
use App\Models\QuestionAnswerUser;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HousemateController extends BaseApiController
{
    use Common_trait;

    public function index($propertyId, Request $request)
    {
        $perPage = $request->input('per_page', config('constants.per_page', 10));
        $isPaginate = filter_var($request->input('is_paginate', 'true'), FILTER_VALIDATE_BOOLEAN);

        $property = Property::find($propertyId);
        if (!$property) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Property']));
        }

        $query = Housemate::with(['images', 'questionsanswer.option'])->where('property_id', $propertyId);

        if ($isPaginate) {
            $housemates = $query->paginate($perPage);
            return ApiResponse::paginate($housemates, HousemateResource::collection($housemates));
        }

        $housemates = $query->get();
        return ApiResponse::success(HousemateResource::collection($housemates));
    }

    public function create(){}

    public function store(Request $request, $propertyId)
    {
        $user = Auth::user();
        $rules = [
            'housemate_id' => 'nullable|exists:' . Housemate::class . ',id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:' . Question::class . ',id',
            'answers.*.option_id' => 'nullable',
            'answers.*.answer' => 'nullable|string',
        ];

        if ($request->filled('housemate_id')) {
            $rules['answers.*.file'] = 'nullable';
        } else {
            $rules['answers.*.file'] = 'nullable|file|mimes:jpg,jpeg,png|max:10240';
        }

        $request->validate($rules);

        $answers = $request->answers;
        $housemateId = $request->housemate_id ?? null;
        $questionAnswerData = [];
        
        $property = Property::find($propertyId);

        if(!$property?->id){
            return ApiResponse::notFound(__('messages.not_found',['item' => 'Property']));
        }

        $propertyStatus = $property->status == '1' ? 1 : 0;

        if($housemateId){
            $questionIds = collect($request->answers)->pluck('question_id')->unique();
            $housemate = Housemate::where('property_id', $propertyId)->find($housemateId);
            if(!$housemate?->id){
                return ApiResponse::notFound(__('messages.not_found',['item' => 'Housemate']));
            }
            $question92 = collect($request->answers)->firstWhere('question_id', 92);
            if(in_array(92, $questionIds->toArray()) && !empty($question92['file'])){
                $this->deleteFile($housemate?->images()->first()->path);
            }
            QuestionAnswerUser::where('user_id', $user->id)
                ->whereIn('question_id', $questionIds)
                ->where('property_id', $propertyId)
                ->where('housemate_id', $housemateId)
                ->forceDelete();
        }else{
            $housemate = $property->housemates()->create([
                'property_id' => $propertyId,
                'status' => $propertyStatus,                
            ]);
        }
        DB::beginTransaction();
        try {
            $housemateId = $housemate->id;
            foreach($answers as $housemateAnswers){
                $question = \App\Models\Question::find($housemateAnswers['question_id']);
                // Handle file upload for question 92
                if ($question->id === 92 && $housemateId && !empty($housemateAnswers['file'])) {
                    $file = $housemateAnswers['file'];
                    $uploadBasePath = 'property/' . $propertyId . '/housemates/' . $housemateId;
                    $filePath = $this->file_upload($file, $uploadBasePath);
                    $housemate->images()->create([
                        'path' => $filePath['original'],
                        'type' => $filePath['type'] === 'image' ? 0 : 1,
                        'thumbnail_path' => $filePath['thumbnail']
                    ]);
                }
                // if(!empty($housemateAnswers['option_id']) || !empty($housemateAnswers['answer'])){
                    $data = $this->manageAllQueAns($housemateAnswers, $question, $user, $propertyId, $housemateId, null);
                    $questionAnswerData = array_merge($questionAnswerData, $data);
                // }
            }
            if(!empty($questionAnswerData)){
                // insert housemate data
                QuestionAnswerUser::insert($questionAnswerData);
            }
            DB::commit();
            return ApiResponse::success(new HousemateResource($housemate),__('messages.success_msg',['item'=> 'Housemate created']));
        } catch (\Exception $e) {
            DB::rollBack();
            $error = $e->getMessage() . ' - ' . $e->getFile() . ' - ' . $e->getLine();
            Log::error($error);
            return ApiResponse::notFound(__('messages.something_error'));
        }
    }
    
    public function edit($id){}
    
    public function update(Request $request, $id){}

    public function show($propertyId, $id)
    {
        $property = Property::find($propertyId);

        // Check if property exists
        if (!$property) {
            return ApiResponse::notFound(__('messages.not_found',['item' => 'Property']));
        }

        $housemate = $property->housemates()->with(['images','questionsanswer.option'])->find($id);
        // Check if room exists
        if (!$housemate) {
            return ApiResponse::notFound(__('messages.not_found',['item' => 'Housemate']));
        }
        
        return ApiResponse::success(new HousemateResource($housemate));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($propertyId, string $id)
    {
        try{      
            DB::beginTransaction();
            $property = Property::find($propertyId);
            if(!$property?->id){
                return ApiResponse::notFound(__('messages.not_found',['item' => 'Property']));
            }

            $housemate = $property->housemates()?->find($id);
           
            // if($housemate->status === 1){
            //     return ApiResponse::notFound(__('messages.not_found',['item' => 'Inactive Housemate']));
            // }

            if(!$housemate?->id){
                return ApiResponse::notFound(__('messages.not_found',['item' => 'Housemate']));
            }

            if($housemate?->images){
                foreach ($housemate->images as $image) {
                    if (file_exists(public_path(($image->path)))) {
                        unlink(public_path($image->path));
                    }
                    $image->forceDelete();
                }
            }
            
            QuestionAnswerUser::whereHousemateId($id)->delete();
            $housemate->delete();

            DB::commit();
            return ApiResponse::success([],__('messages.success_msg',['item' => 'Housemate deleted']));
        }catch (\Exception $e) {
            DB::rollBack(); 
            return ApiResponse::notFound(__('messages.something_went_wrong'));
        }
    }

    public function statusUpdate(Request $request, $propertyId, $housemateId){
        $property = Property::find($propertyId);

        $statusArray = [
            '0' => 'Deactivated',
            '1' => 'Activated',
        ];

        if(!$property?->id){
            return ApiResponse::notFound(__('messages.not_found',['item' => 'Property']));
        }

        $request->validate([
            'status' => 'required|boolean',
        ]);

        $housemate = $property->housemates?->find($housemateId);

        if(!$housemate?->id){
            return ApiResponse::notFound(__('messages.not_found',['item' => 'Housemate']));
        }

        $housemate->status = $request->status;
        $housemate->save();

        return ApiResponse::success(new HousemateResource($housemate),__('messages.success_msg',['item' => 'Housemate ' . $statusArray[$request->status]]));
    }
}
