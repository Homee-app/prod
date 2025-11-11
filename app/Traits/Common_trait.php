<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\UploadedFile;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


trait Common_trait
{
    public function create_unique_slug($string = '', $table = '', $field = 'slug', $col_name = null, $old_slug = null)
    {
        $slug = Str::of($string)->slug('-');
        $slug = strtolower($slug);

        $i = 0;
        $params = array();
        $params[$field] = $slug;
        if ($col_name) {
            $params["$col_name"] = "<> $old_slug";
        }

        while (DB::table($table)->where($params)->count()) {
            if (!preg_match('/-{1}[0-9]+$/', $slug)) {
                $slug .= '-' . ++$i;
            } else {
                $slug = preg_replace('/[0-9]+$/', ++$i, $slug);
            }
            $params[$field] = $slug;
        }
        return $slug;
    }

    public function file_upload($file, $path)
    {
        $disk = config('constants.file_upload_location', 'public'); // fallback to public
        $extension = $file->getClientOriginalExtension();
        $mimeType  = $file->getMimeType();
        $filename = Str::uuid() . '.' . $extension;
        $thumbName = 'thumb-' . $filename;
        $destinationPath = public_path($path);
        $type = null;
        $thumbPath = null;
        $desk = Storage::disk($disk);
        try {
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Save original file
            $fullFilePath = $destinationPath . '/' . $filename;
            $file->move($destinationPath, $filename);

            $fullThumbPath = $destinationPath . '/' . $thumbName;
            if (str_starts_with($mimeType, 'image/')) {
                $type = 'image';
                // Now create thumbnail from saved file (not from $file)
                $manager = new ImageManager(new Driver());
                $manager->read($fullFilePath)->resize(300, null, fn($con) =>  $con->aspectRatio()->upsize())->save($fullThumbPath, 50); // save at 50% quality
                $thumbPath = "{$path}/{$thumbName}";
            } elseif (str_starts_with($mimeType, 'video/')) {
                $type = 'video';
            } else {
                $type = 'chat';
            }
            // Set safe permissions
            chmod($fullFilePath, 0644);
            if ($thumbPath) {
                chmod($fullThumbPath, 0644);
            }

            if ($disk === 's3') {
                $desk->putFileAs($path, new \Illuminate\Http\File($fullFilePath), $filename, 'public');
                if ($thumbPath) {
                    $desk->putFileAs($path, new \Illuminate\Http\File($fullThumbPath), $thumbName, 'public');
                }
                return [
                    'original' => $desk->url("{$path}/{$filename}") ?? null,
                    'thumbnail' => $thumbPath ? $desk->url("{$path}/{$thumbName}") : null,
                    'type' => $type
                ];
            }
            return [
                'original' => "{$path}/{$filename}",
                'thumbnail' => $thumbPath,
                'type' => $type
            ];
        } catch (\Exception $e) {
            Log::error("File upload failed: " . $e->getMessage());
            return null;
        }
    }

    public function deleteFile($filePath)
    {
        if ($filePath) {
            $disk = config('constants.file_upload_location');
            if (Storage::disk($disk)->exists($filePath)) {
                Storage::disk($disk)->delete($filePath);
                return true;
            } else if ($filePath && file_exists(public_path(($filePath)))) {
                unlink(public_path($filePath));
                return true;
            }
        }

        return false;
    }

    public function sendOTP($to = '', $data = [], $message = '')
    {
        $msg = $this->replacePlaceholders($data, $message);

        //return true;

        $postData = [
            'To' => $to,
            'From' => '+' . env('TWILIO_FROM_NUMBER'),
            'Body' => $msg,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.twilio.com/2010-04-01/Accounts/' . env('TWILIO_ACCOUNT_SID') . '/Messages.json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . base64_encode(env('TWILIO_ACCOUNT_SID') . ':' . env('TWILIO_AUTH_TOKEN'))
            ),
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            Log::error("Twilio verification code sending failed", [
                'to' => $to,
                'error' => $error
            ]);
            return false;
        } else {
            Log::info("Twilio verification code sent successfully", [
                'to' => $to,
                'response' => $response
            ]);
            return true;
        }
    }

    public function sendEmail(Mailable $mailable, $email = '',): bool
    {
        try {
            Mail::to($email)->send($mailable);

            Log::info("OTP email sent successfully to: " . $email);
            return true;
        } catch (\Exception $e) {
            // Log failure
            Log::error("Failed to send OTP email to: " . $email . ". Error: " . $e->getMessage());

            return false;
        }
    }

    function replacePlaceholders($replacements,  $message): string
    {
        $hasPlaceholder = false;

        foreach ($replacements as $key => $value) {
            if (strpos($message, "##$key##") !== false) {
                $hasPlaceholder = true;
                $message = str_replace("##$key##", $value, $message);
            }
        }
        return $hasPlaceholder ? $message : $message;
    }

    public function makeQandA($answers)
    {
        $response = [];

        foreach ($answers as $answer) {
            $question = $answer->question;

            if (!$question) {
                continue;
            }

            $questionKey = $question->slug;
            $type = $question->type_for_app;

            switch ($type) {
                case 1: // Single choice
                    $response[$questionKey] = optional($answer->option)->label_for_app ?? $answer->answer;
                    break;

                case 2: // Multiple choice
                    if ($answers instanceof \Illuminate\Support\Collection) {
                        $sameAnswers = $answers->where('question_id', $answer->question_id);
                        $labels = $sameAnswers->pluck('option.label_for_app')->filter()->toArray();
                        $response[$questionKey] = implode(', ', $labels);
                        // Collect icons (extra key, like wifi_icon)
                        $icons = $sameAnswers->pluck('option.image')->filter()->map(fn($img) => asset($img))->toArray();
                        $response[$questionKey . '-icon'] = implode(', ', $icons);
                    } else {
                        $response[$questionKey] = optional($answer->option)->label_for_app;
                        $response[$questionKey . '-icon'] = optional($answer->option)->image ? [optional($answer->option)->image] : [];
                    }
                    break;

                case 3: // Text
                    $response[$questionKey] = $answer->answer;
                    break;

                case 4: // Slider (boolean-like)
                    $response[$questionKey] = $answer->answer ? true : false;
                    break;

                default:
                    $response[$questionKey] = $answer->answer ?? optional($answer->option)->label_for_app;
            }
        }

        return $response;
    }

    public function makeQueAns($answers, $user, $propertyId)
    {
        if (Cache::has('user')) {
            $user = Cache::get('user');
        }
        $questionAnswerData  = [];
        $data = [];
        $null = null;
        foreach ($answers as $answerData) {
            if (isset($answerData['question_id'])) {
                $question = \App\Models\Question::find($answerData['question_id']);
                $data = $this->manageAllQueAns($answerData, $question, $user, $propertyId, $null, $null);
                $questionAnswerData = array_merge($questionAnswerData, $data);
            } else if (isset($answerData['housemate'])) {
                $createdHousemate = \App\Models\Housemate::create([
                    'property_id' => $propertyId,
                    'status' => true,
                ]);
                $housemateId = $createdHousemate->id;
                foreach ($answerData['housemate'] as $housematesAnswers) {
                    $question = \App\Models\Question::find($housematesAnswers['question_id']);
                    $filePath = null;
                    // Handle file upload for question 92
                    if ($question->id === 92 && $housemateId && !empty($housematesAnswers['file'])) {
                        $file = $housematesAnswers['file'];
                        $uploadBasePath = 'property/' . $propertyId . '/housemates/' . $housemateId;
                        if ($file instanceof UploadedFile) {
                            $filePath = $this->file_upload($file, $uploadBasePath);
                        } elseif (is_string($file) && filter_var($file, FILTER_VALIDATE_URL)) {
                            $filePath['original'] = $file;
                        }
                        if ($filePath['original']) {
                            $createdHousemate->images()->create([
                                'path' => $filePath['original'],
                                'type' => isset($filePath['type']) ? ($filePath['type'] === 'image' ? 0 : 1) : 0,
                                'thumbnail_path' => isset($filePath['thumbnail']) ? $filePath['thumbnail'] : null,
                            ]);
                        }
                    }
                    $data = $this->manageAllQueAns($housematesAnswers, $question, $user, $propertyId, $housemateId, $null);
                    $questionAnswerData = array_merge($questionAnswerData, $data);
                }
            } else if (isset($answerData['rooms'])) {
                $createNewRoom = \App\Models\Room::create([
                    'property_id' => $propertyId,
                    'status' => true,
                ]);
                $roomId = $createNewRoom->id;
                foreach ($answerData['rooms'] as $roomAnswers) {
                    $question = \App\Models\Question::find($roomAnswers['question_id']);
                    // Handle file upload for question 92
                    if ($question->id === 86 && $roomId && !empty($roomAnswers['file'])) {
                        $files = $roomAnswers['file'];
                        $uploadBasePath = 'property/' . $propertyId . '/rooms/' . $roomId;
                        foreach ($files as $file) {
                            if ($file instanceof UploadedFile) {
                                $filePath = $this->file_upload($file, $uploadBasePath);
                            } elseif (is_string($file) && filter_var($file, FILTER_VALIDATE_URL)) {
                                $filePath['original'] = $file;
                            }
                            if ($filePath['original']) {
                                $createNewRoom->images()->create([
                                    'path' => $filePath['original'],
                                    'type' => isset($filePath['type']) ? ($filePath['type'] === 'image' ? 0 : 1) : 0,
                                    'thumbnail_path' => isset($filePath['thumbnail']) ? $filePath['thumbnail'] : null,
                                ]);
                            }
                        }
                    }
                    $data = $this->manageAllQueAns($roomAnswers, $question, $user, $propertyId, $null, $roomId);
                    $questionAnswerData = array_merge($questionAnswerData, $data);
                }
            }
        }
        return $questionAnswerData;
    }

    public function manageAllQueAns($answerData, $question, $user, $propertyId, $housematesId = null, $roomId = null)
    {
        $newData = [];
        $created_at = Carbon::now();
        $updated_at = Carbon::now();

        if (Cache::has('user')) {
            $user = Cache::get('user');
        }

        if (isset($answerData['question_id'])) {
            $forPartner = $answerData['for_partner'] ?? false;
            switch ($question->type_for_app) {
                case 1: // single_choice
                    if (isset($answerData['option_id']) && is_array($answerData['option_id'])) {
                        foreach ($answerData['option_id'] as $key => $optionId) {
                            $newData[] = [
                                'user_id' => $user->id,
                                'for_partner' => $forPartner,
                                'question_id' => $answerData['question_id'],
                                'option_id' => $optionId,
                                'answer' => $answerData['answer'],
                                'property_id' => $propertyId,
                                'created_at' => $created_at,
                                'updated_at' => $updated_at,
                                'housemate_id' => $housematesId,
                                'room_id' => $roomId
                            ];
                        }
                    } else if (isset($answerData['option_id'])) { // single_choice + text
                        $optionId =  $answerData['option_id'];
                        $newData[] = [
                            'user_id' => $user->id,
                            'for_partner' => $forPartner,
                            'question_id' => $answerData['question_id'],
                            'option_id' => $optionId,
                            'answer' => $answerData['answer'],
                            'property_id' => $propertyId,
                            'created_at' => $created_at,
                            'updated_at' => $updated_at,
                            'housemate_id' => $housematesId,
                            'room_id' => $roomId
                        ];
                    }
                    break;
                case 4: // slider
                    $newData[] = [
                        'user_id' => $user->id,
                        'for_partner' => $forPartner,
                        'question_id' => $answerData['question_id'],
                        'option_id' => null,
                        'answer' => $answerData['answer'],
                        'property_id' => $propertyId,
                        'created_at' => $created_at,
                        'updated_at' => $updated_at,
                        'housemate_id' => $housematesId,
                        'room_id' => $roomId
                    ];
                    break;
                case 2: // multiple_choice
                    if (isset($answerData['option_id'])) {
                        $optionIds = $this->normalizeOptionIds($answerData['option_id']);
                        Log::info('This is a options as per question: ' . $question->id);
                        Log::info('Options are: ');
                        Log::info($optionIds);
                        foreach ($optionIds as $k => $optionId) {
                            $newData[] = [
                                'user_id' => $user->id,
                                'for_partner' => $forPartner,
                                'question_id' => $answerData['question_id'],
                                'option_id' => $optionId,
                                'answer' => null,
                                'property_id' => $propertyId,
                                'created_at' => $created_at,
                                'updated_at' => $updated_at,
                                'housemate_id' => $housematesId,
                                'room_id' => $roomId
                            ];
                        }
                    }
                    break;
                case 3: // text
                    $newData[] = [
                        'user_id' => $user->id,
                        'for_partner' => $forPartner,
                        'question_id' => $answerData['question_id'],
                        'option_id' => null,
                        'answer' => $answerData['answer'] ?? null,
                        'property_id' => $propertyId,
                        'created_at' => $created_at,
                        'updated_at' => $updated_at,
                        'housemate_id' => $housematesId,
                        'room_id' => $roomId
                    ];
                    break;
            }
        }
        return $newData;
    }

    public function normalizeOptionIds($input)
    {
        if (is_array($input)) {
            return array_map('intval', $input);
        }

        if (is_string($input)) {
            $input = trim($input);

            if (str_starts_with($input, '[') && str_ends_with($input, ']')) {
                $decoded = json_decode($input, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return array_map('intval', $decoded);
                }
                $input = trim($input, '[]');
            }

            $parts = explode(',', $input);
            return array_map(fn($v) => intval(trim($v)), $parts);
        }

        // Fallback → wrap in array
        return [(int)$input];
    }


    public function makeQuestionsGroupBy($user, $propertyId = null, $roomId = null, $housemateId = null)
    {
        $sectionArray = [
            'profile' => [1],
            'property' => [2, 4],
            'room' => [3],
        ];

        $scope = 'profile'; // default
        if ($propertyId && $roomId && $housemateId) {
            $scope = 'housemate';
        } elseif ($propertyId && $roomId) {
            $scope = 'room';
        } elseif ($propertyId) {
            $scope = 'property';
        }

        $sectionType = $sectionArray[$scope] ?? 1;
        $query = \App\Models\Question::with([
            'options:id,question_id,label_for_app,value,min_val,max_val',
            'userAnswer' => function ($query) use ($user, $propertyId, $roomId, $housemateId) {
                $query->where('user_id', $user->id)
                    ->where('for_partner', false)
                    ->when($propertyId, fn($q) => $q->wherePropertyId((int)  $propertyId))
                    ->when($roomId, fn($q) => $q->whereRoomId((int) $roomId)->whereNull('housemate_id'))
                    ->when($housemateId, fn($q) => $q->whereHousemateId((int) $housemateId)->whereNull('room_id'))
                    ->select('question_id', 'option_id', 'answer', 'id', 'for_partner', 'housemate_id', 'room_id', 'property_id');
            },
            'partnerAnswer' => function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('for_partner', true)
                    ->select('question_id', 'option_id', 'answer', 'id', 'for_partner');
            },
            'privacySetting' => function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->select('question_id', 'is_hidden');
            }
        ])->whereIn('section', $sectionType);

        if ($propertyId) {
            $query = $query->whereHas('userAnswer', function ($query) use ($user, $propertyId, $roomId, $housemateId) {
                $query->where('user_id', $user->id)
                    ->when($propertyId, fn($q) => $q->wherePropertyId((int)  $propertyId))
                    ->when($roomId, fn($q) => $q->whereRoomId((int) $roomId)->whereNull('housemate_id'))
                    ->when($housemateId, fn($q) => $q->whereHousemateId((int) $housemateId)->whereNull('room_id'))
                    ->where('for_partner', false);
            });
        }

        $query = $query->select('id', 'title_for_app', 'sub_title_for_app', 'type_for_app', 'question_order_for_app', 'section', 'question_for')
            ->orderBy('question_for')
            ->orderBy('question_order_for_app')
            ->get()
            ->groupBy('question_for');

        $data = $query->map(function ($groupedQuestions, $screenId) {
            return [
                'screen_id' => $screenId,
                'questions' => \App\Http\Resources\QuestionResource::collection($groupedQuestions)
            ];
        })->values();
        return $data;
    }

    public function deleteAllImages($images)
    {
        if ($images && !empty($images) && count($images) > 0) {
            foreach ($images as $image) {
                $this->deleteFile($image->path);
                $image->forceDelete();
            }
        }
        return true;
    }

    public function getOptionsIdValues($questionId)
    {
        return Cache::remember("options_id_{$questionId}",  now()->addDays(1), function () use ($questionId) {
            return \App\Models\QuestionsOption::whereQuestionId($questionId)->pluck('id', 'label_for_app')->toArray() ?? [];
        });
    }
}
