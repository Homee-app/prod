<?php

namespace App\Http\Controllers\api\v1;

use App\Constants\QuestionConstants;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\QuestionAnswerUser;
use App\Http\Resources\UserResource;
use App\Http\Resources\QuestionResource;
use Illuminate\Support\Facades\Auth;
use App\Models\UserQuestionPrivacy;
use App\Models\TenantProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QuestionController extends BaseApiController
{

    use Common_trait;

    public function index(Request $request)
    {
        $user = Auth::user();
        $sectionArray = [
            'propfile' => [1],
            'property' => [2, 4],
            'room' => [3],
        ];
        $type = $request->type ?? null;

        $questions = Question::whereNull('deleted_at')->when($type, function ($que) use ($type, $sectionArray) {
            $que->whereIn('section', $type ? $sectionArray[$type] : [1]);
        })
            ->orderBy('question_order_for_app')
            ->with(['options' => function ($query) {
                $query->select('id', 'question_id', 'label_for_app', 'value', 'image');
            }])
            ->get(['id', 'title_for_app', 'sub_title_for_app', 'type_for_app', 'question_for']);

        $formatted = $questions->map(function ($question) {
            return [
                'question_id' => $question->id,
                'title'       => $question->title_for_app,
                'subtitle'    => $question->sub_title_for_app,
                'question_for' => $question->question_for,
                'selection_type' => match ($question->type_for_app) {
                    1 => 'single_choice',
                    2 => 'multiple_choice',
                    3 => 'text',
                    4 => 'slider',
                    5 => 'info',
                    default => 'unknown',
                },
                'options' => $question->options->map(function ($option) {
                    return [
                        'option_id' => $option->id,
                        'label'     => $option->label_for_app,
                        'value'     => $option->value,
                        'image'     => $option->image ? asset($option->image) : null,
                        'sublevel_option' => $option->sublevel_for_app,
                    ];
                }),
            ];
        });

        return $this->sendResponse($formatted,  __('messages.fetche_success', ['item' => 'Questions fetched']));
    }

    public function tenantsFilter(Request $request)
    {
        $user = Auth::user();
        $questionIds = [23, 22, 4, 5, 29, 21, 18, 6, 7, 8, 9, 30, 5, 31, 26, 28, 24, 27];

        $questions = Question::whereNull('deleted_at')
            ->whereIn('id', $questionIds)
            ->with(['options' => function ($query) {
                $query->select('id', 'question_id', 'label_for_app', 'value');
            }])
            ->get(['id', 'title_for_app', 'sub_title_for_app', 'type_for_app', 'question_for']);

        // Reorder collection to match the original order in $questionIds
        $questions = $questions->sortBy(function ($q) use ($questionIds) {
            return array_search($q->id, $questionIds);
        })->values();

        $formatted = $questions->map(function ($question) {
            return [
                'question_id' => $question->id,
                'title'       => $question->title_for_app,
                'subtitle'    => $question->sub_title_for_app,
                'question_for' => $question->question_for,
                'selection_type' => match ($question->type_for_app) {
                    1 => 'single_choice',
                    2 => 'multiple_choice',
                    3 => 'text',
                    4 => 'slider',
                    5 => 'info',
                    default => 'unknown',
                },
                'options' => $question->options->map(function ($option) {
                    return [
                        'option_id' => $option->id,
                        'label'     => $option->label_for_app,
                        'value'     => $option->value,
                    ];
                }),
            ];
        });

        // $formatted = QuestionResource::collection($questions);

        return $this->sendResponse($formatted, __('messages.fetche_success', ['item' => 'Questions fetched']));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.option_id' => 'nullable',
            'answers.*.answer' => 'nullable|string',
            'answers.*.for_partner' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $questionIds = collect($request->answers)->pluck('question_id')->unique();

            $currentAnswerForQ1 = QuestionAnswerUser::where('user_id', $user->id)
                ->where('question_id', 1)
                ->where('for_partner', false)
                ->first();

            QuestionAnswerUser::where('user_id', $user->id)
                ->whereIn('question_id', $questionIds)
                ->delete();

            $shouldDeletePartnerData = false;

            $lifestylePreferences = [];

            $lifestyleQuestionMap = [
                10 => 'morning_vs_night',
                11 => 'cleanliness_preference',
                12 => 'introversion_vs_extroversion',
                13 => 'temperature_sensitivity',
                14 => 'cooking_vs_eating_out',
                15 => 'homebody_vs_outgoing',
                16 => 'sharing_preference',
                17 => 'social_hosting_preference',
            ];

            foreach ($request->answers as $answerData) {
                $question = Question::find($answerData['question_id']);
                $forPartner = $answerData['for_partner'] ?? false;

                if (isset($answerData['hide_on_profile'])) {
                    UserQuestionPrivacy::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'question_id' => $answerData['question_id'],
                        ],
                        [
                            'is_hidden' => (bool) $answerData['hide_on_profile'],
                        ]
                    );
                }

                if (isset($lifestyleQuestionMap[$question->id])) {
                    $value = (int) $answerData['answer'];
                    if ($value >= 1 && $value <= 10) {
                        $lifestylePreferences[$lifestyleQuestionMap[$question->id]] = $value;
                    }
                }

                if ($question->id === 1) {
                    $newSelectedOptionId = is_array($answerData['option_id']) ? ($answerData['option_id'][0] ?? null) : ($answerData['option_id'] ?? null);

                    if ($currentAnswerForQ1 && $currentAnswerForQ1->option_id == 2 && $newSelectedOptionId == 1) {
                        $shouldDeletePartnerData = true;
                    }
                }

                if ($question->id === 19) {
                    $isTeamup = false;

                    $selectedOptionId = is_array($answerData['option_id'])
                        ? ($answerData['option_id'][0] ?? null)
                        : ($answerData['option_id'] ?? null);

                    if ($selectedOptionId) {
                        $selectedOption = $question->options->firstWhere('id', $selectedOptionId);
                        if ($selectedOption && $selectedOption->label_for_app === 'Yes') {
                            $isTeamup = true;
                        }
                    }

                    TenantProfile::updateOrCreate(
                        ['user_id' => $user->id],
                        ['is_teamup' => $isTeamup]
                    );
                }

                switch ($question->type_for_app) {
                    case 1: // single_choice
                        if (isset($answerData['option_id']) && is_array($answerData['option_id'])) {
                            foreach ($answerData['option_id'] as $key => $optionId) {
                                QuestionAnswerUser::create([
                                    'user_id' => $user->id,
                                    'for_partner' => $forPartner,
                                    'question_id' => $answerData['question_id'],
                                    'option_id' => $optionId,
                                    'answer' => $answerData['answer']
                                ]);
                            }
                        } else if (isset($answerData['option_id'])) { // single_choice + text
                            $optionId =  $answerData['option_id'];
                            QuestionAnswerUser::create([
                                'user_id' => $user->id,
                                'for_partner' => $forPartner,
                                'question_id' => $answerData['question_id'],
                                'option_id' => $optionId,
                                'answer' => $answerData['answer']
                            ]);
                        }
                        break;
                    case 4: // slider
                        QuestionAnswerUser::create([
                            'user_id' => $user->id,
                            'for_partner' => $forPartner,
                            'question_id' => $answerData['question_id'],
                            'option_id' => null,
                            'answer' => $answerData['answer']
                        ]);
                        break;

                    case 2: // multiple_choice
                        if (isset($answerData['option_id'])) {
                            $optionIds = is_array($answerData['option_id']) ?
                                $answerData['option_id'] :
                                [$answerData['option_id']];

                            foreach ($optionIds as $optionId) {
                                QuestionAnswerUser::create([
                                    'user_id' => $user->id,
                                    'for_partner' => $forPartner,
                                    'question_id' => $answerData['question_id'],
                                    'option_id' => $optionId,
                                    'answer' => null
                                ]);
                            }
                        }
                        break;

                    case 3: // text
                        QuestionAnswerUser::create([
                            'user_id' => $user->id,
                            'for_partner' => $forPartner,
                            'question_id' => $answerData['question_id'],
                            'option_id' => null,
                            'answer' => $answerData['answer'] ?? null
                        ]);
                        break;
                }
            }

            if (!empty($lifestylePreferences)) {

                if (!empty($lifestylePreferences)) {
                    // Check if tenant profile already exists
                    $tenantProfile = TenantProfile::where('user_id', $user->id)->first();

                    if (!$tenantProfile) {
                        // Create new instance
                        $tenantProfile = new TenantProfile();
                        $tenantProfile->user_id = $user->id;
                    }

                    // Fill in lifestyle preferences
                    foreach ($lifestylePreferences as $key => $value) {
                        $tenantProfile->$key = $value;
                    }

                    $tenantProfile->save();
                }
            }

            if ($shouldDeletePartnerData) {
                QuestionAnswerUser::where('user_id', $user->id)->where('for_partner', true)->delete();
            }

            DB::commit();

            return $this->sendResponse([
                'user' => new UserResource($user),
            ], 'Questions submitted');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendResponse([], __('messages.failed_to_submit_questions'), 500);
        }
    }

    public function getByScreen(Request $request, $screen_ids)
    {
        $user = $request->user();
        $propertyId = $request->property_Id ?? null;
        $roomId = $request->room_id ?? null;
        $housemateId = $request->housemate_id ?? null;
        $screenIdsArray = array_map('intval', explode(',', $screen_ids));

        $questions = Question::with([
            'options:id,question_id,label_for_app,value,min_val,max_val',
            'userAnswer' => function ($query) use ($user, $propertyId, $roomId, $housemateId) {
                $query->where('user_id', $user->id)
                    ->when($propertyId, fn($q) => $q->wherePropertyId((int)  $propertyId))
                    ->when($roomId, fn($q) => $q->whereRoomId((int) $roomId)->whereNull('housemate_id'))
                    ->when($housemateId, fn($q) => $q->whereHousemateId((int) $housemateId)->whereNull('room_id'))
                    ->where('for_partner', false)
                    ->select('question_id', 'option_id', 'answer', 'id', 'for_partner', 'property_id', 'room_id', 'housemate_id');
            },
            'partnerAnswer' => function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('for_partner', true)
                    ->select('question_id', 'option_id', 'answer', 'id', 'for_partner', 'property_id', 'room_id', 'housemate_id');
            },
            'privacySetting' => function ($query) use ($user) {
                $query->where('user_id', $user->id)->select('question_id', 'is_hidden');
            }
        ])
            ->whereHas('userAnswer', function ($query) use ($user, $propertyId, $roomId, $housemateId) {
                $query->where('user_id', $user->id)
                    ->when($propertyId, fn($q) => $q->wherePropertyId((int)  $propertyId))
                    ->when($roomId, fn($q) => $q->whereRoomId((int) $roomId)->whereNull('housemate_id'))
                    ->when($housemateId, fn($q) => $q->whereHousemateId((int) $housemateId)->whereNull('room_id'))
                    ->where('for_partner', false);
            })
            ->select('id', 'title_for_app', 'sub_title_for_app', 'type_for_app', 'question_order_for_app', 'section', 'question_for')
            ->when(!empty($screenIdsArray), function ($q) use ($screenIdsArray) {
                $q->whereIn('question_for', $screenIdsArray);
            })
            ->orderBy('question_order_for_app')
            ->get();

        $data = QuestionResource::collection($questions);

        return response()->json([
            'status' => true,
            'message' => 'Questions fetched successfully.',
            'data' => $data,
        ]);
    }

    public function profileStatus(Request $request)
    {
        $user = $request->user();

        $question1 = Question::with([
            'userAnswer' => function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('for_partner', false)
                    ->select('question_id', 'option_id', 'answer', 'id', 'for_partner');
            }
        ])
            ->find(1); // Find question with ID 1 directly

        // Profile is considered set up if question ID 1 exists AND has a user answer
        $hasProfileSetup = (bool) ($question1 && $question1->userAnswer->isNotEmpty());

        $profileSetup = (bool) $hasProfileSetup;
        $subscription = $user->active_subscription;
        return response()->json([
            'status' => $profileSetup,
            'message' => $profileSetup
                ? 'Profile is set up.'
                : 'Profile not set up yet. Please complete the initial profile questions.',
            'profile_setup' => $profileSetup,
            'name' => $user->name,
            'image' => $user?->profile_photo
                ? asset($user->profile_photo)
                : asset('/public/assets/images/dummy-image.jfif'),
            'boost' => [
                'count' => $user->boost_count ?? 0,
                'status' => $user->is_boost_applied,
                'date' => $user->is_boost_applied
                    ? optional($user->boost_expired_time)
                    ? Carbon::parse($user->boost_expired_time)->setTimezone('UTC')
                    : null
                    : null,
            ],
            'key' => [
                'count' => $user->key_count ?? 0,
                'status' => $user->is_key_applied,
                'date' => $user->is_key_applied
                    ? optional($user->key_applied_time)
                    ? Carbon::parse($user->key_applied_time)->setTimezone('UTC')
                    : null
                    : null,
            ],
            'property_status' => $user->can_add_property,
            'room_status' => $user->can_add_room,
            'allowed_chat' => $user->can_chat,
            'is_subscribed' => (($subscription?->role?->id == $user->role) ? true : false),
        ]);
    }

    public function getGroupedByScreen(Request $request)
    {
        $user = $request->user();
        // this is a traid function for reuse perpose
        $data = $this->makeQuestionsGroupBy($user);

        $profileSections = [];

        foreach ($data as $screen) {
            $screenId = $screen['screen_id'];
            $questions = $screen['questions'];

            if ($screenId === 1) { // Who’s moving in?
                $movingInQuestion = $questions[0]->toArray($request);
                $movingInLabel = '';

                $selectedOptionId = $movingInQuestion['user_answer']['option_id'][0] ?? null;

                foreach ($movingInQuestion['options'] as $opt) {
                    $option = $opt->toArray($request);
                    if ($option['id'] === $selectedOptionId) {
                        $movingInLabel = $option['label'];
                        break;
                    }
                }

                $profileSections[] = [
                    'screen_id' => $screenId,
                    'section' => 'Who\'s moving in', // or "Moving in status"
                    'value' => $movingInLabel,
                    'is_competed' => $movingInLabel === '' ? false : true
                ];
            }

            if ($screenId === 2) { // About you
                $name = $questions[0]->toArray($request)['user_answer']['answer'] ?? '';
                $rawDob = $questions[1]->toArray($request)['user_answer']['answer'] ?? null;

                $formattedDob = '';
                if ($rawDob) {
                    try {
                        $formattedDob = Carbon::parse($rawDob)->format('d/m/Y');
                    } catch (\Exception $e) {
                        $formattedDob = $rawDob; // Fallback to raw if parsing fails
                    }
                }

                // Combine name and DOB only if present
                $combined = implode(', ', array_filter([$name, $formattedDob]));

                $profileSections[] = [
                    'screen_id' => $screenId,
                    'section' => 'About you',
                    'value' => $combined,
                    'is_competed' => $combined === '' ? false : true
                ];
            }

            if ($screenId === 3) { // Your identity
                $genderQuestion = $questions[0]->toArray($request);
                $sexualityQuestion = $questions[1]->toArray($request);

                $genderLabel = '';
                $sexualityLabel = '';

                // Extract gender label
                $genderOptionId = $genderQuestion['user_answer']['option_id'][0] ?? null;
                foreach ($genderQuestion['options'] as $opt) {
                    $option = $opt->toArray($request);
                    if ($option['id'] === $genderOptionId) {
                        $genderLabel = $option['label'];
                        break;
                    }
                }

                // Extract sexuality label
                $sexualityOptionId = $sexualityQuestion['user_answer']['option_id'][0] ?? null;
                foreach ($sexualityQuestion['options'] as $opt) {
                    $option = $opt->toArray($request);
                    if ($option['id'] === $sexualityOptionId) {
                        $sexualityLabel = $option['label'];
                        break;
                    }
                }

                // Combine for output
                $value = trim("{$genderLabel}, {$sexualityLabel}", ", ");

                $profileSections[] = [
                    'screen_id' => $screenId,
                    'section' => 'Your identity',
                    'value' => $value,
                    'is_competed' => $value === '' ? false : true
                ];
            }

            if ($screenId === 6) { // Your Lifestyle
                $morningNightQuestion = null;
                $cleanlinessQuestion = null;
                $introExtroQuestion = null;
                $temperatureQuestion = null;
                $cookingEatingOutQuestion = null;
                $homebodyQuestion = null;
                $sharingPrefQuestion = null;
                $socialHostQuestion = null;

                foreach ($questions as $q) {
                    $questionArray = $q->toArray($request);
                    switch ($questionArray['question_id']) {
                        case 10:
                            $morningNightQuestion = $questionArray;
                            break;
                        case 11:
                            $cleanlinessQuestion = $questionArray;
                            break;
                        case 12:
                            $introExtroQuestion = $questionArray;
                            break;
                        case 13:
                            $temperatureQuestion = $questionArray;
                            break;
                        case 14:
                            $cookingEatingOutQuestion = $questionArray;
                            break;
                        case 15:
                            $homebodyQuestion = $questionArray;
                            break;
                        case 16:
                            $sharingPrefQuestion = $questionArray;
                            break;
                        case 17:
                            $socialHostQuestion = $questionArray;
                            break;
                    }
                }

                $morningNightValue = (int)($morningNightQuestion['user_answer']['answer'] ?? 0);
                $cleanlinessValue = (int)($cleanlinessQuestion['user_answer']['answer'] ?? 0);
                $introExtroValue = (int)($introExtroQuestion['user_answer']['answer'] ?? 0);
                $temperatureValue = (int)($temperatureQuestion['user_answer']['answer'] ?? 0);
                $cookingEatingOutValue = (int)($cookingEatingOutQuestion['user_answer']['answer'] ?? 0);
                $homebodyValue         = (int)($homebodyQuestion['user_answer']['answer'] ?? 0);
                $sharingPrefValue      = (int)($sharingPrefQuestion['user_answer']['answer'] ?? 0);
                $socialHostValue = (int)($socialHostQuestion['user_answer']['answer'] ?? 0);


                $habitDescriptions = [];

                // Morning person or night owl (assuming 1-10 scale, 1 being morning, 10 being night)
                if ($morningNightValue > 0) { // Check if an answer exists
                    if ($morningNightValue <= 3) {
                        $habitDescriptions[] = 'a morning person';
                    } elseif ($morningNightValue >= 8) {
                        $habitDescriptions[] = 'a night owl';
                    } else {
                        $habitDescriptions[] = 'somewhere in between morning and night';
                    }
                }


                // Clean freak or easygoing cleaner (assuming 1-10 scale, 1 being clean freak, 10 being easygoing)
                if ($cleanlinessValue > 0) { // Check if an answer exists
                    if ($cleanlinessValue <= 3) {
                        $habitDescriptions[] = 'a clean freak';
                    } elseif ($cleanlinessValue >= 8) {
                        $habitDescriptions[] = 'an easygoing cleaner';
                    } else {
                        $habitDescriptions[] = 'fairly tidy';
                    }
                }

                // Introverted or extroverted (assuming 1-10 scale, 1 being introverted, 10 being extroverted)
                if ($introExtroValue > 0) { // Check if an answer exists
                    if ($introExtroValue <= 3) {
                        $habitDescriptions[] = 'more introverted';
                    } elseif ($introExtroValue >= 8) {
                        $habitDescriptions[] = 'more extroverted';
                    } else {
                        $habitDescriptions[] = 'a mix of both introverted and extroverted traits';
                    }
                }

                if ($temperatureValue > 0) {
                    if ($temperatureValue <= 3) {
                        $habitDescriptions[] = 'sensitive to cold';
                    } elseif ($temperatureValue >= 8) {
                        $habitDescriptions[] = 'sensitive to heat';
                    } else {
                        $habitDescriptions[] = 'comfortable in most temperatures';
                    }
                }

                // Cooking vs Eating Out
                if ($cookingEatingOutValue > 0) {
                    if ($cookingEatingOutValue <= 3) {
                        $habitDescriptions[] = 'primarily cook at home';
                    } elseif ($cookingEatingOutValue >= 8) {
                        $habitDescriptions[] = 'mostly eat out';
                    } else {
                        $habitDescriptions[] = 'balance cooking and dining out';
                    }
                }

                // Homebody vs Outgoing
                if ($homebodyValue > 0) {
                    if ($homebodyValue <= 3) {
                        $habitDescriptions[] = 'a homebody';
                    } elseif ($homebodyValue >= 8) {
                        $habitDescriptions[] = 'very outgoing';
                    } else {
                        $habitDescriptions[] = 'sometimes out and sometimes at home';
                    }
                }

                // Sharing Preference
                if ($sharingPrefValue > 0) {
                    if ($sharingPrefValue <= 3) {
                        $habitDescriptions[] = 'prefer to share things';
                    } elseif ($sharingPrefValue >= 8) {
                        $habitDescriptions[] = 'like to keep things separate';
                    } else {
                        $habitDescriptions[] = 'flexible with sharing';
                    }
                }

                // Social Host vs Reserved (assuming 1-10 scale)
                if ($socialHostValue > 0) {
                    if ($socialHostValue <= 3) {
                        $habitDescriptions[] = 'more reserved socially';
                    } elseif ($socialHostValue >= 8) {
                        $habitDescriptions[] = 'a social host';
                    } else {
                        $habitDescriptions[] = 'occasionally enjoys hosting';
                    }
                }


                // Combine descriptions into a human-readable string
                $value = '';
                if (!empty($habitDescriptions)) {
                    if (count($habitDescriptions) === 1) {
                        $value = "Typically " . $habitDescriptions[0] . ".";
                    } elseif (count($habitDescriptions) === 2) {
                        $value = "They are " . implode(' and ', $habitDescriptions) . ".";
                    } else {
                        $lastHabit = array_pop($habitDescriptions);
                        $value = "They are " . implode(', ', $habitDescriptions) . ", and " . $lastHabit . ".";
                    }
                }

                $profileSections[] = [
                    'screen_id' => $screenId,
                    'section' => 'Your Lifestyle',
                    'value' => $value,
                    'is_competed' => $value === '' ? false : true
                ];
            }

            if ($screenId === 7) { // Your ethnicity
                $ethnicityQuestion = $questions[0]->toArray($request);

                $ethnicityOptionId = $ethnicityQuestion['user_answer']['option_id'][0] ?? null;
                $ethnicityLabel = '';

                foreach ($ethnicityQuestion['options'] as $opt) {
                    $opt = is_array($opt) ? $opt : $opt->toArray($request); // handle resource case
                    if (
                        ($opt['id'] ?? $opt['option_id']) == $ethnicityOptionId
                    ) {
                        $ethnicityLabel = $opt['label'];
                        break;
                    }
                }

                $profileSections[] = [
                    'screen_id' => $screenId,
                    'section' => 'Your ethnicity',
                    'value' => $ethnicityLabel,
                    'is_competed' => $ethnicityLabel === '' ? false : true
                ];
            }

            if ($screenId === 8) { // Home Preferences
                $teamUpsQuestion = null;
                $suburbsQuestion = null;
                $stayLengthQuestion = null;

                foreach ($questions as $q) {
                    $questionArray = $q->toArray($request);
                    if ($questionArray['question_id'] === 19) {
                        $teamUpsQuestion = $questionArray;
                    } elseif ($questionArray['question_id'] === 20) {
                        $suburbsQuestion = $questionArray;
                    } elseif ($questionArray['question_id'] === 21) {
                        $stayLengthQuestion = $questionArray;
                    }
                }

                $preferenceParts = [];

                // 1. Open to Team Ups? (Question ID 19)
                $selectedTeamUpsOptionId = $teamUpsQuestion['user_answer']['option_id'][0] ?? null;
                if ($teamUpsQuestion && $selectedTeamUpsOptionId !== null) {
                    foreach ($teamUpsQuestion['options'] as $opt) {
                        $option = $opt->toArray($request);
                        if ($option['id'] === $selectedTeamUpsOptionId) {
                            $preferenceParts[] = "Open to Team Ups: " . $option['label'];
                            break;
                        }
                    }
                }

                // 2. What suburbs are you interested in? (Question ID 20)
                if (!empty($suburbsQuestion['user_answer']['suburbs'])) {
                    $suburbNames = [];
                    foreach ($suburbsQuestion['user_answer']['suburbs'] as $suburb) {
                        $suburbNames[] = $suburb['name'] . ' (' . $suburb['state'] . ')';
                    }

                    if (!empty($suburbNames)) {
                        $preferenceParts[] = "Interested Suburbs: " . implode(', ', $suburbNames);
                    }
                }

                // 2. Preferred Stay Length (Question ID 21)
                $selectedStayLengthOptionId = $stayLengthQuestion['user_answer']['option_id'][0] ?? null;
                if ($stayLengthQuestion && $selectedStayLengthOptionId !== null) {
                    foreach ($stayLengthQuestion['options'] as $opt) {
                        $option = $opt->toArray($request);
                        if ($option['id'] === $selectedStayLengthOptionId) {
                            $preferenceParts[] = "Preferred Stay Length: " . $option['label'];
                            break;
                        }
                    }
                }

                // Show section with either partial or full values (or empty value)
                $value = !empty($preferenceParts) ? implode(', ', $preferenceParts) : '';
                $profileSections[] = [
                    'screen_id' => $screenId,
                    'section' => 'Your Home Preferences',
                    'value' => $value,
                    'is_competed' => $value === '' ? false : true
                ];
            }

            if ($screenId === 10) { // Tell Us More About You
                $languagesQuestion = null;
                $politicalViewsQuestion = null;
                $overnightGuestsQuestion = null;

                foreach ($questions as $q) {
                    $questionArray = $q->toArray($request);
                    if ($questionArray['question_id'] === 26) {
                        $languagesQuestion = $questionArray;
                    } elseif ($questionArray['question_id'] === 27) {
                        $politicalViewsQuestion = $questionArray;
                    } elseif ($questionArray['question_id'] === 28) {
                        $overnightGuestsQuestion = $questionArray;
                    }
                }

                $aboutYouParts = [];

                // 1. Languages Spoken
                $selectedLanguageOptionIds = $languagesQuestion['user_answer']['option_id'] ?? [];
                if (!empty($languagesQuestion) && !empty($selectedLanguageOptionIds)) {
                    $languageLabels = [];
                    foreach ($languagesQuestion['options'] as $opt) {
                        $option = $opt->toArray($request);
                        if (in_array($option['id'], $selectedLanguageOptionIds)) {
                            $languageLabels[] = $option['label'];
                        }
                    }
                    if (!empty($languageLabels)) {
                        $aboutYouParts[] = implode(', ', $languageLabels);
                    }
                }

                // 2. Political Views
                $selectedPoliticalOptionId = $politicalViewsQuestion['user_answer']['option_id'][0] ?? null;
                if ($politicalViewsQuestion && $selectedPoliticalOptionId !== null) {
                    foreach ($politicalViewsQuestion['options'] as $opt) {
                        $option = $opt->toArray($request);
                        if ($option['id'] === $selectedPoliticalOptionId) {
                            $aboutYouParts[] = "Political views: '" . strtolower($option['label']) . "'";
                            break;
                        }
                    }
                }

                // 3. Overnight Guests
                $selectedGuestOptionId = $overnightGuestsQuestion['user_answer']['option_id'][0] ?? null;
                if ($overnightGuestsQuestion && $selectedGuestOptionId !== null) {
                    foreach ($overnightGuestsQuestion['options'] as $opt) {
                        $option = $opt->toArray($request);
                        if ($option['id'] === $selectedGuestOptionId) {
                            $aboutYouParts[] = "Overnight guests: '" . strtolower($option['label']) . "'";
                            break;
                        }
                    }
                }

                $value = implode(', ', $aboutYouParts);

                $profileSections[] = [
                    'screen_id' => $screenId,
                    'section' => 'Tell Us More About You',
                    'value' => $value, // Can be empty if no answers, but section will still show
                    'is_competed' => $value === '' ? false : true
                ];
            }

            if ($screenId === 12) { // Your religious beliefs
                $religionQuestion = $questions[0]->toArray($request);

                $religionOptionId = $religionQuestion['user_answer']['option_id'][0] ?? null;
                $religionLabel = '';

                foreach ($religionQuestion['options'] as $opt) {
                    $opt = is_array($opt) ? $opt : $opt->toArray($request); // convert if it's a resource
                    if (
                        ($opt['id'] ?? $opt['option_id']) == $religionOptionId
                    ) {
                        $religionLabel = $opt['label'];
                        break;
                    }
                }

                $profileSections[] = [
                    'screen_id' => $screenId,
                    'section' => 'Your religious beliefs',
                    'value' => $religionLabel,
                    'is_competed' => $religionLabel === '' ? false : true
                ];
            }

            if ($screenId === 4) { // Your profession
                $professionQuestion = $questions[0]->toArray($request);

                $professionOptionId = $professionQuestion['user_answer']['option_id'][0] ?? null;
                $professionLabel = '';

                foreach ($professionQuestion['options'] as $opt) {
                    $opt = is_array($opt) ? $opt : $opt->toArray($request);
                    if (
                        ($opt['id'] ?? $opt['option_id']) == $professionOptionId
                    ) {
                        $professionLabel = $opt['label'];
                        break;
                    }
                }

                $profileSections[] = [
                    'screen_id' => $screenId,
                    'section' => 'Your employment',
                    'value' => $professionLabel,
                    'is_competed' => $professionLabel === '' ? false : true
                ];
            }

            if ($screenId === 5) { // Your lifestyle and habits
                $drinkingQuestion = $questions[0]->toArray($request);
                $smokingQuestion = $questions[1]->toArray($request);
                $dietaryQuestion  = $questions[2]->toArray($request); // Question ID 9

                $drinkingStatus = '';
                $smokingStatus = '';
                $dietaryLabels = [];

                // Determine drinking status label
                $drinkingOptionId = $drinkingQuestion['user_answer']['option_id'][0] ?? null;
                foreach ($drinkingQuestion['options'] as $opt) {
                    $option = is_array($opt) ? $opt : $opt->toArray($request);
                    if ($option['id'] === $drinkingOptionId && in_array($option['label'], ['Yes', 'Occasionally'])) {
                        $drinkingStatus = 'Drinking';
                        break;
                    }
                }

                // Determine smoking status label
                $smokingOptionId = $smokingQuestion['user_answer']['option_id'][0] ?? null;
                foreach ($smokingQuestion['options'] as $opt) {
                    $option = is_array($opt) ? $opt : $opt->toArray($request);
                    if ($option['id'] === $smokingOptionId && in_array($option['label'], ['Yes', 'Occasionally'])) {
                        $smokingStatus = 'Smoking';
                        break;
                    }
                }

                // Handle dietary requirements (can be multiple)
                $selectedDietaryIds = $dietaryQuestion['user_answer']['option_id'] ?? [];
                foreach ($dietaryQuestion['options'] as $opt) {
                    $option = is_array($opt) ? $opt : $opt->toArray($request);
                    if (in_array($option['id'], $selectedDietaryIds ?? [])) {
                        $dietaryLabels[] = $option['label'];
                    }
                }

                // Build final value string
                $valueParts = [];
                if ($drinkingStatus) {
                    $valueParts[] = $drinkingStatus;
                }
                if ($smokingStatus) {
                    $valueParts[] = $smokingStatus;
                }
                if (!empty($dietaryLabels)) {
                    $valueParts[] = implode(', ', $dietaryLabels);
                }

                $value = implode(', ', $valueParts);

                $profileSections[] = [
                    'screen_id' => $screenId,
                    'section' => 'Your lifestyle and habits',
                    'value' => $value,
                    'is_competed' => $value === '' ? false : true
                ];
            }

            if ($screenId === 11) { // Your interests
                $interestsQuestion = $questions[0]->toArray($request); // Assuming interests is the first question on this screen

                $interestLabels = [];
                // user_answer['option_id'] for multiple choice is an array of selected option IDs
                $selectedOptionIds = $interestsQuestion['user_answer']['option_id'] ?? [];

                foreach ($interestsQuestion['options'] as $opt) {
                    $option = $opt->toArray($request);
                    if (in_array($option['id'], $selectedOptionIds)) {
                        // Remove emojis from the label before adding
                        $cleanLabel = preg_replace('/[^\p{L}\p{N}\s]/u', '', $option['label']);
                        $cleanLabel = trim($cleanLabel); // Trim any extra spaces
                        if (!empty($cleanLabel)) {
                            $interestLabels[] = $cleanLabel;
                        }
                    }
                }

                // Combine the selected interests into a comma-separated string
                $value = implode(', ', $interestLabels);

                $profileSections[] = [
                    'screen_id' => $screenId,
                    'section' => 'Your interests',
                    'value' => $value,
                    'is_competed' => $value === '' ? false : true
                ];
            }

            if ($screenId === 13) { // Tell Us More About You (Text questions)
                $answeredQuestions = [];

                foreach ($questions as $question) {
                    $questionArray = $question->toArray($request);
                    $answer = $questionArray['user_answer']['answer'] ?? null;

                    if (!empty(trim($answer))) {
                        $title = $questionArray['title']; // or $questionArray['title_for_app'] if you use that
                        $answeredQuestions[] = "{$title}: " . trim($answer);
                    }
                }

                $value = implode(', ', $answeredQuestions); // Combine all answered question/answer pairs

                $profileSections[] = [
                    'screen_id' => $screenId,
                    'section' => 'Your prompts',
                    'value' => $value, // Will be empty string if no questions answered
                    'is_competed' => $value === '' ? false : true
                ];
            }
        }

        if (!empty($profileSections)) {
            $profileSections[] = [
                'screen_id' => 15,
                'section' => 'Your profile images',
                'value' => '',
                'is_competed' => $user->profile_photo ? true : false
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Questions grouped by screen fetched successfully.',
            'profile' => $profileSections,
        ]);
    }

    public function profilePercentage(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $response = findUserPercentage($userId);
        return response()->json($response);
    }

}
