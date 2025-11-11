<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionAnswerUser;
use App\Models\TenantProfile;
use App\Models\User;
use App\Models\UserQuestionPrivacy;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class UserQuestionsAnswersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $questions = Question::with('options')->get()->keyBy('id');
        $tenantUsers = User::where('role', 2)->get(); 

        $this->command->info('Seeding question answers for tenants...');

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

        foreach ($tenantUsers as $user) {
            $answersToInsert = [];
            $privacyToInsert = [];
            $isTeamupForUser = false; 
            $lifestylePreferences = [];

            foreach ($lifestyleQuestionMap as $questionId => $columnName) {
                $value = $faker->numberBetween(1, 10);
                $lifestylePreferences[$columnName] = $value;

                $answersToInsert[] = [
                    'user_id' => $user->id,
                    'question_id' => $questionId,
                    'for_partner' => false,
                    'option_id' => null,
                    'answer' => (string) $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $q1OptionId = $faker->boolean(70) ? 1 : 2; 
            $willHavePartnerData = ($q1OptionId === 2); 

            $q19Options = $questions->get(19)?->options;
            $q19YesOptionId = $q19Options?->firstWhere('label_for_app', 'Yes')?->id;
            $q19NoOptionId = $q19Options?->firstWhere('label_for_app', 'No')?->id;

            if ($q19YesOptionId && $q19NoOptionId) {
                $selectedQ19OptionId = $faker->boolean(50) ? $q19YesOptionId : $q19NoOptionId; 
                $isTeamupForUser = ($selectedQ19OptionId === $q19YesOptionId);
            } else {
                $selectedQ19OptionId = null;
            }   

            foreach ($questions as $question) {
                $questionId = $question->id;

                if (isset($lifestyleQuestionMap[$questionId])) {
                    continue;
                }

                $forPartner = false; 
                if ($willHavePartnerData && $faker->boolean(30) && $question->id > 1 && $question->id < 19) { 
                     $forPartner = true;
                }

                $answerData = [
                    'user_id' => $user->id,
                    'question_id' => $questionId,
                    'for_partner' => $forPartner,
                    'option_id' => null,
                    'answer' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                if ($questionId === 1) { 
                    $answerData['option_id'] = $q1OptionId;
                    $answerData['answer'] = null; 
                } elseif ($questionId === 19) { 
                    $answerData['option_id'] = $selectedQ19OptionId;
                    $answerData['answer'] = null; // No direct answer text for Q19
                } elseif ($questionId === 2) { // Q2: Varsha S (name input)
                     $answerData['answer'] = $faker->name();
                } elseif ($questionId === 3) { // Q3: DOB
                     $answerData['answer'] = $faker->date('d/m/Y', '2005-01-01');
                } elseif ($questionId === 5) { // Q5: Example of hide_on_profile
                    $privacyToInsert[] = [
                        'user_id' => $user->id,
                        'question_id' => $questionId,
                        'is_hidden' => $faker->boolean(70), // 70% chance to hide
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                    // Pick a random option for Q5 as it's a single choice in your JSON
                    $randomOption = $question->options->random();
                    $answerData['option_id'] = $randomOption->id;
                    $answerData['answer'] = null;
                } elseif ($questionId === 22) { // Q22: Move-in date
                     $answerData['answer'] = $faker->dateTimeBetween('+1 week', '+6 months')->format('Y-m-d');
                } elseif ($questionId === 23) { // Q23: Rent per week (min,max)
                    $min = $faker->numberBetween(200, 600); // e.g., 400
                    $max = $faker->numberBetween($min + 50, $min + 400); // e.g., 800
                    $answerData['answer'] = "$min,$max";
                }
                else {
                    // General logic based on question type
                    switch ($question->type_for_app) {
                        case 1: // single_choice
                            if ($question->options->isNotEmpty()) {
                                $answerData['option_id'] = $question->options->random()->id;
                            }
                            break;
                        case 2: // multiple_choice
                            if ($question->options->count() > 1) {
                                // Pick 1 to 3 random options
                                $optionIds = $question->options->random(rand(1, min(3, $question->options->count())))->pluck('id')->toArray();
                                // For multiple choice, we need to create multiple entries or handle as array if your model supports it
                                // Given your API, it creates multiple entries for multiple_choice options
                                foreach($optionIds as $optionId) {
                                    $answersToInsert[] = [
                                        'user_id' => $user->id,
                                        'question_id' => $questionId,
                                        'for_partner' => $forPartner,
                                        'option_id' => $optionId,
                                        'answer' => null,
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ];
                                }
                                continue 2; // Skip default insertion below as we inserted multiple
                            }
                            break;
                        case 3: // text
                            $answerData['answer'] = $faker->sentence(rand(3, 8));
                            break;
                        case 4: // slider (assuming answers are numerical strings)
                            $sliderValue = $faker->numberBetween(1, 10);
                            $answerData['answer'] = (string) $sliderValue;

                            // Check if it's one of the lifestyle questions
                            if (isset($lifestyleQuestionMap[$questionId]) && !$forPartner) {
                                $lifestylePreferences[$lifestyleQuestionMap[$questionId]] = $sliderValue;
                            }
                            break;
                        case 5: // info (no answer to store in QuestionAnswerUser)
                            continue 2; // Skip this question completely for answersToInsert
                            break;
                    }
                }

                // Add to batch for insertion, but only if option_id or answer is set
                // (except for multiple_choice handled above)
                if ($answerData['option_id'] !== null || $answerData['answer'] !== null) {
                    $answersToInsert[] = $answerData;
                }
            } // End of foreach ($questions as $question)

            // --- Database Insertions ---
            DB::transaction(function () use ($user, $answersToInsert, $privacyToInsert, $isTeamupForUser, $lifestylePreferences) {
                // Clear existing answers for this user for a fresh seed
                QuestionAnswerUser::where('user_id', $user->id)->delete();
                UserQuestionPrivacy::where('user_id', $user->id)->delete();

                if (!empty($answersToInsert)) {
                    QuestionAnswerUser::insert($answersToInsert);
                }

                if (!empty($privacyToInsert)) {
                    UserQuestionPrivacy::insert($privacyToInsert);
                }

                TenantProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    array_merge(
                        ['is_teamup' => $isTeamupForUser],
                        $lifestylePreferences
                    )
                );
            });

            $this->command->info("Seeded answers for user: {$user->email}");
        }

        $this->command->info('All tenant question answers seeded successfully.');
    }
}