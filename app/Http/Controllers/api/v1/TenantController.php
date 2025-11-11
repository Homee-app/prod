<?php

namespace App\Http\Controllers\api\v1;

use App\Constants\QuestionConstants;
use App\Helpers\ApiResponse;
use App\Models\PropertyOwner;
use App\Models\UserQuestionPrivacy;
use Illuminate\Http\Request;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\NearbyTenantRequest;
use App\Models\Property;
use App\Services\NearbyTenantService;
use Illuminate\Support\Facades\Log;

class TenantController extends BaseApiController
{
    public function getLifestyleMatch(Request $request, $viewed_user_id)
    {
        $currentUser = $request->user();
        $viewedUser = User::find($viewed_user_id);

        if (!$viewedUser) {
            return $this->sendError(__('messages.not_found', ['item' => 'Viewed user']), [], 404);
        }

        $lifestyleQuestionIds = [10, 11, 12, 13, 14, 15, 16, 17];

        $currentUserAnswers = $this->getSliderAnswersForUser($currentUser->id, $lifestyleQuestionIds);
        $viewedUserAnswers = $this->getSliderAnswersForUser($viewedUser->id, $lifestyleQuestionIds);

        $matchPercentage = $this->_calculateLifestyleMatchPercentage(
            $currentUserAnswers,
            $viewedUserAnswers,
            $lifestyleQuestionIds
        );

        if ($matchPercentage === null) {
            return $this->sendError(__('messages.no_common_lifestyle'), [], 400);
        }

        return $this->sendResponse([
            'lifestyle_match_percentage' => $matchPercentage
        ], 'Lifestyle match calculated successfully.');
    }

    private function getSliderAnswersForUser($userId, array $questionIds)
    {
        $questionToProfileField = [
            10 => 'morning_vs_night',
            11 => 'cleanliness_preference',
            12 => 'introversion_vs_extroversion',
            13 => 'temperature_sensitivity',
            14 => 'cooking_vs_eating_out',
            15 => 'homebody_vs_outgoing',
            16 => 'sharing_preference',
            17 => 'social_hosting_preference',
        ];

        $profile = TenantProfile::where('user_id', $userId)->first();

        $answers = [];

        if ($profile) {
            foreach ($questionIds as $questionId) {
                if (isset($questionToProfileField[$questionId])) {
                    $column = $questionToProfileField[$questionId];
                    $answers[$questionId] = (int) $profile->$column;
                }
            }
        }

        return $answers;
    }

    private function _calculateLifestyleMatchPercentage(array $answers1, array $answers2, array $questionIds): ?float
    {
        $totalSimilarity = 0;
        $answeredQuestionCount = 0;
        $maxDifference = 10; // Assuming slider values are 1-10, so max difference is 9 (10-1 or 1-10) is 9. Using 10 as per previous code.

        foreach ($questionIds as $questionId) {
            $answer1 = $answers1[$questionId] ?? null;
            $answer2 = $answers2[$questionId] ?? null;

            if ($answer1 !== null && $answer2 !== null) {
                $difference = abs($answer1 - $answer2);
                $similarity = 1 - ($difference / $maxDifference);

                $totalSimilarity += $similarity;
                $answeredQuestionCount++;
            }
        }

        if ($answeredQuestionCount > 0) {
            $averageSimilarity = $totalSimilarity / $answeredQuestionCount;
            return round($averageSimilarity * 100); // Round to whole number
        }

        return null; // No common questions answered
    }

    public function getNearbyTenants(NearbyTenantRequest $request)
    {
        $currentUser = $request->user();
        $start = microtime(true);
        // Step 1: Extract validated filters
        $filters = $request->validated();

        // Log the request filters to custom log
        Log::channel('nearbytenants')->info('NearbyTenantsSearch Request Filters', [
            'user_id' => $currentUser->id ?? null,
            'filters' => $filters,
        ]);

        if ($currentUser->role == 3) {
            $owner = PropertyOwner::withCount('properties')
                ->where('user_id', $currentUser->id)
                ->first();

            if (!$owner || $owner->properties_count == 0) {
                return ApiResponse::success([], __('messages.no_owner_found'));
            }
        }

        $lifestyleQuestionIds = QuestionConstants::SESSION_QUESTIONS_FOR['lifestyle_question_ids'];

        // Step 2: Validate current user location
        if (is_null($currentUser->latitude) || is_null($currentUser->longitude)) {
            $requestedSuburbs = $filters['suburb_ids'] ?? [];
            $userSuburbs = $currentUser->suburbs()->pluck('option_id')->toArray();
            $filters['suburb_ids'] = array_unique(array_merge($requestedSuburbs, $userSuburbs));
        }

        $currentTenantProfile = TenantProfile::where('user_id', $currentUser->id)->first();
        if (!$currentTenantProfile) {
            return $this->sendResponse([], __('messages.tenant_profile_not_found'), 404);
        }

        if (!$currentTenantProfile->is_teamup) {
            return $this->sendResponse([], __('messages.enable_open_to_team_ups'), 403);
        }

        $currentProfile = $currentUser->tenantProfile;
        $currentUserAnswers = collect($lifestyleQuestionIds)->mapWithKeys(function ($id) use ($currentProfile) {
            return [$id => (int) optional($currentProfile)->{config('lifestyle.column_map')[$id] ?? null}];
        })->toArray();

        // Step 4: Call the service
        $service = new NearbyTenantService(
            $currentUser,
            $filters,
            $currentUserAnswers,
            $lifestyleQuestionIds
        );

        $tenants = $service->getNearbyTenants($request->get('per_page', 15));

        $executionTime = number_format(microtime(true) - $start, 4) . ' seconds';

        // Log execution time
        Log::channel('nearbytenants')->info('NearbyTenantsSearch Execution Time', [
            'user_id' => $currentUser->id,
            'time' => $executionTime,
            'results_count' => $tenants->total(),
        ]);

        $message = $tenants->isEmpty()
            ? ($currentUser->role == 2
                ? __('messages.tenant_no_data_found')
                : __('messages.owner_no_data_found'))
            : 'Nearby tenants fetched successfully';
        // Step 5: Return formatted response

        return response()->json([
            'status' => true,
            'message' => $message,
            'execution_time' => $executionTime,
            'total' => $tenants->total(),
            'data' => UserResource::collection($tenants->items()),
            'pagination' => [
                'total' => $tenants->total(),
                'per_page' => $tenants->perPage(),
                'current_page' => $tenants->currentPage(),
                'last_page' => $tenants->lastPage(),
                'from' => $tenants->firstItem(),
                'to' => $tenants->lastItem(),
            ],
        ]);
    }

    public function getTenantDetails(Request $request, $id)
    {
        $user = User::with(['tenantProfile', 'questionAnswers.question', 'questionAnswers.option', 'userIdentity', 'suburbs'])->find($id);

        // if (!$user || $user->role != 2) {
        //     return response()->json(['success' => false, 'message' => 'Tenant not found.',], 404);
        // }

        $currentUser = Auth::user();

        // Get lifestyle question IDs and map
        $lifestyleQuestionIds = [10, 11, 12, 13, 14, 15, 16, 17];
        $columnMap = config('lifestyle.column_map');

        $userAnswers = collect($lifestyleQuestionIds)->mapWithKeys(function ($id) use ($user) {
            $answer = $user->questionAnswers
                ->where('question_id', $id)
                ->where('for_partner', false)
                ->first();

            return [$id => $answer && is_numeric($answer->answer) ? (int) $answer->answer : null];
        })->toArray();

        // Lifestyle preference label mapping
        $lifestyleLabels = [
            10 => ['☀️ Morning Person', '🌛 Night Owl'],
            11 => ['🧼 Clean Freak', '😎 Easygoing Cleaner'],
            12 => ['🤭 Introverted', '😜 Extroverted'],
            13 => ['♨️ I Feel Hot Easily', '❄️ I Feel Cold Easily'],
            14 => ['👨‍🍳 I Cook More', '🥡 I Eat Out More'],
            15 => ['🏡 Homebody', '🌆 Always Out and About'],
            16 => ['🛒 Prefer to Share', '💰 Prefer to Keep Things Separate'],
            17 => ['🧑‍🤝‍🧑 Social Host', '💁‍♀️ Reserved'],
        ];

        $lifestylePreferences = [];

        foreach ($userAnswers as $questionId => $answerValue) {
            if (!$answerValue) continue;

            $label = null;
            $percentage = null;

            if ($answerValue >= 1 && $answerValue <= 5) {
                $map = [1 => 100, 2 => 80, 3 => 60, 4 => 40, 5 => 20];
                $label = $lifestyleLabels[$questionId][0] ?? null;
                $percentage = $map[$answerValue] ?? null;
            } else if ($answerValue >= 6 && $answerValue <= 10) {
                $map = [6 => 20, 7 => 40, 8 => 60, 9 => 80, 10 => 100];
                $label = $lifestyleLabels[$questionId][1] ?? null;
                $percentage = $map[$answerValue] ?? null;
            }

            if ($label) {
                // $lifestylePreferences[] = $label;
                $lifestylePreferences[] = [
                    'label' => $label,
                    'percentage' => $percentage
                ];
            }
        }

        // Current user profile & answers
        $currentTenantProfile = TenantProfile::where('user_id', $currentUser->id)->first();

        $currentUserAnswers = $this->getSliderAnswersForUser($currentUser->id, $lifestyleQuestionIds);
        $viewedUserAnswers = $this->getSliderAnswersForUser($user->id, $lifestyleQuestionIds);

        $lifestyleMatch = $this->_calculateLifestyleMatchPercentage(
            $currentUserAnswers,
            $viewedUserAnswers,
            $lifestyleQuestionIds
        );

        $dobAnswer = $user->questionAnswers->where('question_id', 3)->where('for_partner', false)->first();

        $dob = $dobAnswer ? Carbon::createFromFormat('d/m/Y', $dobAnswer->answer) : null;
        $age = $dob ? $dob->age : null;

        // Get availability date from answer (question_id = 22)
        $availabilityAnswer = $user->questionAnswers->firstWhere('question_id', 22);
        try {
            $availabilityDate = $availabilityAnswer
                ? Carbon::createFromFormat('d/m/Y', $availabilityAnswer->answer)->format('j F Y')
                : null;
        } catch (\Exception $e) {
            $availabilityDate = null;
        }

        // Gender (question_id = 4)
        $genderAnswer = $user->questionAnswers->where('question_id', 4)->where('for_partner', false)->first();
        $gender = $genderAnswer && $genderAnswer->option ? $genderAnswer->option->label_for_app : null;

        // Room Furnishings (question_id = 25)
        $roomFurnishingAnswer = $user->questionAnswers->where('question_id', 25)->where('for_partner', false)->first();
        $roomFurnishings = $roomFurnishingAnswer && $roomFurnishingAnswer->option ? $roomFurnishingAnswer->option->label_for_app : null;

        // Bathroom Furnishings (question_id = 83)
        $bathroomFurnishingAnswer = $user->questionAnswers->where('question_id', 83)->where('for_partner', false)->first();
        $bathroomFurnishings = $bathroomFurnishingAnswer && $bathroomFurnishingAnswer->option ? $bathroomFurnishingAnswer->option->label_for_app : null;

        // Max number of house mates Furnishings (question_id = 85)
        $maxNumberOfHouseMatesFurnishingAnswer = $user->questionAnswers->where('question_id', 85)->where('for_partner', false)->first();
        $maxNumberOfHouseMatesFurnishings = $maxNumberOfHouseMatesFurnishingAnswer && $maxNumberOfHouseMatesFurnishingAnswer->option ? $maxNumberOfHouseMatesFurnishingAnswer->option->label_for_app : null;

        // Internet Furnishings (question_id = 82)
        $internetFurnishingAnswer = $user->questionAnswers->where('question_id', 82)->where('for_partner', false)->first();
        $internetFurnishings = $internetFurnishingAnswer && $internetFurnishingAnswer->option ? $internetFurnishingAnswer->option->label_for_app : null;

        // Parking Furnishings (question_id = 84)
        $parkingFurnishingAnswer = $user->questionAnswers->where('question_id', 84)->where('for_partner', false)->first();
        $parkingFurnishings = $parkingFurnishingAnswer && $parkingFurnishingAnswer->option ? $parkingFurnishingAnswer->option->label_for_app : null;

        // Preferred Suburbs (question_id = 20)
        $suburbOptionIds = $user->questionAnswers->where('question_id', 20)->pluck('option_id')->filter()->unique()->toArray();
        $preferredSuburbs = \App\Models\Suburb::whereIn('id', $suburbOptionIds)->get(['id', 'name', 'postcode']);
        $formattedSuburbs = $preferredSuburbs->map(function ($suburb) {
            return $suburb->name . ', ' . $suburb->postcode;
        })->values();

        // Ethnicity (question_id = 18)
        $ethnicityAnswer = $user->questionAnswers->where('question_id', 18)->where('for_partner', false)->first();
        $partnerEthnicityAnswer = $user->questionAnswers->where('question_id', 18)->where('for_partner', true)->first();
        $ethnicity = $ethnicityAnswer && $ethnicityAnswer->option ? $ethnicityAnswer->option->label_for_app : null;
        $partnerEthnicity = $partnerEthnicityAnswer && $partnerEthnicityAnswer->option ? $partnerEthnicityAnswer->option->label_for_app : null;

        // Sexuality (question_id = 5)
        $sexualityAnswer = $user->questionAnswers->where('question_id', 5)->where('for_partner', false)->first();
        $partnerSexualityAnswer = $user->questionAnswers->where('question_id', 5)->where('for_partner', true)->first();
        $sexuality = $sexualityAnswer && $sexualityAnswer->option ? $sexualityAnswer->option->label_for_app : null;
        $partnerSexuality = $partnerSexualityAnswer && $partnerSexualityAnswer->option ? $partnerSexualityAnswer->option->label_for_app : null;

        // Languages (question_id = 26)
        $languageAnswers = $user->questionAnswers->where('question_id', 26);
        $languages = $languageAnswers->pluck('option.label_for_app')->filter()->values()->all();

        // Religion (question_id = 31)
        $religionAnswer = $user->questionAnswers->where('question_id', 31)->where('for_partner', false)->first();
        $partnerReligionAnswer = $user->questionAnswers->where('question_id', 31)->where('for_partner', true)->first();
        $religions = $religionAnswer && $religionAnswer->option ? [$religionAnswer->option->label_for_app] : [];
        $partnerReligions = $partnerReligionAnswer && $partnerReligionAnswer->option ? [$partnerReligionAnswer->option->label_for_app] : [];

        // Political Views (question_id = 27)
        $politicalAnswers = $user->questionAnswers->where('question_id', 27);
        $politicalViews = $politicalAnswers->pluck('option.label_for_app')->filter()->values()->all();

        // Rental History (question_id = 24)
        $rentalHistoryAnswer = $user->questionAnswers->firstWhere('question_id', 24);

        $rentalHistory = null;

        if ($rentalHistoryAnswer && $rentalHistoryAnswer->option) {
            $label = strtolower($rentalHistoryAnswer->option->label_for_app);

            if ($label === 'yes') {
                $rentalHistory = 'Rental History Available';
            } elseif ($label === 'no') {
                $rentalHistory = 'No Rental History';
            }
        }

        // Open to Guests (question_id = 28)
        $overnightGuestsAnswer = $user->questionAnswers->firstWhere('question_id', 28);
        $openToOvernightGuests = $overnightGuestsAnswer && $overnightGuestsAnswer->option ? $overnightGuestsAnswer->option->label_for_app : null;

        if ($openToOvernightGuests) {
            $guestLabel = null;

            if (strtolower($openToOvernightGuests) === 'yes' || strtolower($openToOvernightGuests) === 'occasionally') {
                $guestLabel = 'Yes to Overnight Guests';
            } elseif (strtolower($openToOvernightGuests) === 'no') {
                $guestLabel = 'No to Overnight Guests';
            } else {
                $guestLabel = $openToOvernightGuests; // fallback for other values if any
            }
        }

        // Employment Status (question_id = 6)
        $employmentAnswer = $user->questionAnswers->where('question_id', 6)->where('for_partner', false)->first();
        $partnerEmploymentAnswer = $user->questionAnswers->where('question_id', 6)->where('for_partner', true)->first();
        $employmentStatus = $employmentAnswer && $employmentAnswer->option ? $employmentAnswer->option->label_for_app : null;
        $partnerEmploymentStatus = $partnerEmploymentAnswer && $partnerEmploymentAnswer->option ? $partnerEmploymentAnswer->option->label_for_app : null;

        // Interests (question_id = 30)
        $interestAnswers = $user->questionAnswers->where('question_id', 30)->pluck('option.label_for_app')->filter()->values()->all();

        $petsAnswer = $user->questionAnswers->firstWhere('question_id', 29);

        $pets = null;

        if ($petsAnswer && $petsAnswer->option) {
            $label = $petsAnswer->option->label_for_app;

            if (str_contains($label, 'Not open')) {
                $pets = 'No pets';
            } elseif (str_contains($label, 'have')) {
                $pets = 'Has a pet';
            } elseif (str_contains($label, 'Open to')) {
                $pets = 'Open to pets';
            } else {
                $pets = $label;
            }
        }

        $drinkAnswer = $user->questionAnswers->firstWhere('question_id', 7);
        $smokeAnswer = $user->questionAnswers->firstWhere('question_id', 8);
        $dietAnswer  = $user->questionAnswers->firstWhere('question_id', 9);

        $habits = [];

        $habitIcons = [
            'drink' => asset('images/habits/habit1.svg'),
            'smoke' => asset('images/habits/habit2.svg'),
            'diet' => asset('images/habits/habit3.svg'),
        ];

        if ($drinkAnswer && $drinkAnswer->option && $this->checkHiddenDetails($user->id, 7)) {
            $habits[] = [
                'label' => $drinkAnswer->option->label_for_app,
                'icon_key' => 'drink',
                'icon_url' => $habitIcons['drink'],
            ];
        }

        if ($smokeAnswer && $smokeAnswer->option && $this->checkHiddenDetails($user->id, 8)) {
            $habits[] = [
                'label' => $smokeAnswer->option->label_for_app,
                'icon_key' => 'smoke',
                'icon_url' => $habitIcons['smoke'],
            ];
        }

        if ($dietAnswer && $dietAnswer->option && $this->checkHiddenDetails($user->id, 9)) {
            $habits[] = [
                'label' => $dietAnswer->option->label_for_app,
                'icon_key' => 'diet',
                'icon_url' => $habitIcons['diet'],
            ];
        }

        // Profile photo
        $profilePhoto = $user->profile_photo ? asset($user->profile_photo) : null;
        $partnerProfilePhoto = $user->partner_profile_photo ? asset($user->partner_profile_photo) : null;

        $userNameAnswer = $user->questionAnswers
            ->where('question_id', 2)
            ->where('for_partner', false)
            ->first();

        $name = $userNameAnswer ? $userNameAnswer->answer : null;

        // Preferred Stay Length (question_id = 21)
        $stayLengthAnswer = $user->questionAnswers->firstWhere('question_id', 21);
        $stayLength = $stayLengthAnswer && $stayLengthAnswer->option ? $stayLengthAnswer->option->label_for_app : null;

        // Rental Budget Range (question_id = 23)
        $rentalBudgetAnswer = $user->questionAnswers->firstWhere('question_id', 23);
        $minBudget = null;
        $maxBudget = null;

        if ($rentalBudgetAnswer && $rentalBudgetAnswer->answer) {
            $budgetParts = explode(',', $rentalBudgetAnswer->answer);
            $minBudget = isset($budgetParts[0]) ? (int) trim($budgetParts[0]) : null;
            $maxBudget = isset($budgetParts[1]) ? (int) trim($budgetParts[1]) : null;
        }

        $userIdentity = $user->userIdentity;

        if ($userIdentity) {
            $verificationStatus = $userIdentity->verification_status;
        } else {
            $verificationStatus = 'not_submitted'; // No identity data
        }

        $answeredQuestionsSection13to16 = $user->questionAnswers
            ->filter(function ($answer) {
                $questionFor = optional($answer->question)->question_for;
                return in_array($questionFor, [13, 14, 15, 16]) && ($answer->option || $answer->answer);
            })
            ->map(function ($answer) {
                return [
                    'question_id' => $answer->question_id,
                    'question' => optional($answer->question)->title_for_app,
                    'answer' => $answer->option
                        ? $answer->option->label_for_app
                        : $answer->answer,
                ];
            })
            ->values();

        // Fetch partner-related answers
        $partnerAnswers = $user->questionAnswers->where('for_partner', true);

        // Partner Name (Q2)
        $partnerNameAnswer = $partnerAnswers->firstWhere('question_id', 2);
        $partnerName = $partnerNameAnswer ? $partnerNameAnswer->answer : null;

        // Partner DOB (Q3)
        $partnerDobAnswer = $partnerAnswers->firstWhere('question_id', 3);

        $partnerDob = $partnerDobAnswer ? Carbon::createFromFormat('d/m/Y', $partnerDobAnswer->answer) : null;
        $partnerAge = $partnerDob ? $partnerDob->age : null;

        // Partner Gender (Q4)
        $partnerGenderAnswer = $partnerAnswers->firstWhere('question_id', 4);
        $partnerGender = $partnerGenderAnswer && $partnerGenderAnswer->option ? $partnerGenderAnswer->option->label_for_app : null;

        // Partner Profile Image
        $partnerImage = $user->partner_profile_photo ? asset($user->partner_profile_photo) : null;
        $languageText = $languages ? 'Speaks ' . implode(', ', $languages) : null;
        $religionText = $religions ? implode(', ', $religions) : null;
        $politicalViewsText = $politicalViews ? implode(', ', array_map(fn($v) => "$v political", $politicalViews)) : null;
        $lifestyleMatchText = $lifestyleMatch !== null ? $lifestyleMatch . '% Lifestyle Match' : null;

        $partner = [];
        if ($partnerName) {
            $partner['name'] = $partnerName;
        }
        if ($partnerImage) {
            $partner['image'] = $partnerImage;
        }
        if ($partnerAge) {
            $partner['age'] = $partnerAge;
        }
        if ($partnerGender && $this->checkHiddenDetails($user->id, 4)) {
            $partner['gender'] = ['label' => $partnerGender, 'icon_url' => asset('images/habits/gender.svg'),];
        }
        if ($partnerEthnicity) {
            $partner['ethnicity'] = $partnerEthnicity;
        }
        if ($partnerReligions && $this->checkHiddenDetails($user->id, 31)) {
            $partner['religions'] = ['label' => $partnerReligions, 'icon_url' => asset('images/habits/religion.svg'),];
        }
        if ($partnerSexuality && $this->checkHiddenDetails($user->id, 5)) {
            $partner['sexuality'] = ['label' => $partnerSexuality, 'icon_url' => asset('images/habits/sexuality.svg'),];
        }
        if ($partnerEmploymentStatus) {
            $partner['employment_status'] = $partnerEmploymentStatus;
        }

        $about = [];
        if ($gender && $this->checkHiddenDetails($user->id, 4)) {
            $about[] = ['label' => $gender, 'icon_url' => asset('images/habits/gender.svg'),];
        }
        if ($ethnicity) {
            $about[] = ['label' => $ethnicity, 'icon_url' => asset('images/habits/ethnicity.svg'),];
        }

        if ($sexuality && $this->checkHiddenDetails($user->id, 5)) {
            $about[] = ['label' => $sexuality, 'icon_url' => asset('images/habits/sexuality.svg'),];
        }
        if ($languageText) {
            $about[] = ['label' => $languageText, 'icon_url' => asset('images/habits/language.svg'),];
        }
        if ($religionText  && $this->checkHiddenDetails($user->id, 31)) {
            $about[] = ['label' => $religionText, 'icon_url' => asset('images/habits/religion.svg'),];
        }
        if ($politicalViewsText && $this->checkHiddenDetails($user->id, 27)) {
            $about[] = ['label' => $politicalViewsText, 'icon_url' => asset('images/habits/politics.svg'),];
        }
        if ($openToOvernightGuests && $this->checkHiddenDetails($user->id, 28)) {
            $about[] = ['label' => $guestLabel, 'icon_url' => asset('images/habits/overnight-guests.svg'),];
        }
        if ($pets) {
            $about[] = ['label' => $pets, 'icon_url' => asset('images/habits/pets.svg'),];
        }
        if ($rentalHistory) {
            $about[] = ['label' => $rentalHistory, 'icon_url' => asset('images/habits/rental-history.svg')];
        }
        if ($lifestyleMatch) {
            $about[] = ['label' => $lifestyleMatchText, 'icon_url' => asset('images/habits/lifestyle-match.svg')];
        }

        $open_to_teamup = $user->questionAnswers->where('question_id', 19)->where('for_partner', false)->first()?->option_id === 44;
        $preamps = "tenantId=" . $id;
        return response()->json([
            'success' => true,
            'data' => [
                'id' => (int) $id,
                'profile_photo' => $profilePhoto,
                'partner_profile_photo' => $partnerProfilePhoto,
                // 'name' => $name,
                'name' => $user->first_name,
                'age' => $age,
                'gender' => $gender,
                'preferred_stay_length' => $stayLength,
                'min_budget' => $minBudget,
                'max_budget' => $maxBudget,
                'open_to_teamup' => $open_to_teamup,
                'is_verified' => $verificationStatus === 'approved',
                'verification_status' => $verificationStatus,
                'room_furnishings' => $roomFurnishings,
                'bathroom_preferences' => $bathroomFurnishings,
                'max_number_of_house_mates_preferences' => $maxNumberOfHouseMatesFurnishings,
                'internet_preferences' => $internetFurnishings,
                'parking_preferences' => $parkingFurnishings,
                'lifestyle_match_percent' => $lifestyleMatch,
                'availability_date' => $availabilityDate ? Carbon::parse($availabilityDate)->format('j F Y') : null,
                'preferred_suburbs' => $formattedSuburbs,
                'about' => $about,
                'ethnicity' => $ethnicity,
                'sexuality' => $sexuality,
                'languages' => $languages,
                'religions' => $religions,
                'political_views' => $politicalViews,
                'rental_history' => $rentalHistory,
                'open_to_overnight_guests' => $openToOvernightGuests,
                'pets' => $pets,
                'interests' => $interestAnswers,
                'employment_status' => $employmentStatus,
                'partner' => $partner,
                'habits' => $habits,
                'lifestyle_preferences' => $lifestylePreferences,
                'answered_questions_sections_13_16' => $answeredQuestionsSection13to16,
                'is_saved' => $user->is_saved ?? false,
                'share_url' => app(\App\Services\DeepLinkService::class)->createDeepLink('tenant-details', $preamps) ?? [],
                'boost' => [
                    'count' => $user->boost_count ?? 0,
                    'status' => $user->is_boost_applied,
                    'date' => $user->is_boost_applied ? $user->boost_expired_time : null,
                ],
                'allowed_chat' => $currentUser->can_chat
            ],
        ]);
    }

    public function toggleLike(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|numeric|exists:' . User::class . ',id',
        ]);
        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }
        if ($user->id == $request->tenant_id) {
            return ApiResponse::notFound(__('messages.tenant_profile_not_found'));
        }
        $tenant = User::find($request->tenant_id);
        if (!$tenant?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Tenant']));
        }
        if ($tenant->role != 2) {
            return ApiResponse::notFound(__('messages.tenant_profile_not_found'));
        }
        $tenantId = $tenant->id;
        if ($user->role == 3) {
            $owner = PropertyOwner::withCount('properties')->where('user_id', $user->id)->first();
            if (!$owner || $owner->properties_count == 0) {
                return ApiResponse::success([], __('messages.no_owner_found'));
            }
            if (!$owner?->id) {
                return ApiResponse::notFound(__('messages.not_found', ['item' => 'Owner']));
            }
            if ($owner->likedTenants()->where('tenant_id', $tenantId)->exists()) {
                $owner->likedTenants()->detach($tenantId); // remove like
                return ApiResponse::success(__('messages.success_msg', ['item' => 'Tenant removed']));
            }
            $owner->likedTenants()->attach($tenantId); // add like
        } else if ($user->role == 2) {
            if ($user->likedTenants()->where('tenant_id', $tenantId)?->exists()) {
                $user->likedTenants()->detach($tenantId); // remove like
                return ApiResponse::success(__('messages.success_msg', ['item' => 'Tenant removed']));
            }
            $user->likedTenants()->attach($tenantId); // add like
        }
        return ApiResponse::success(__('messages.success_msg', ['item' => 'Tenant added']));
    }

    protected function checkHiddenDetails($userId, $questionId)
    {
        return UserQuestionPrivacy::whereUserId($userId)->where('question_id', $questionId)->where('is_hidden', 1)->doesntExist();
    }
}
