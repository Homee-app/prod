<?php

namespace Database\Seeders;

use App\Models\PropertyOwner;
use App\Models\Question;
use App\Models\QuestionsOption;
use App\Models\User;
use App\Models\QuestionAnswerUser;
use App\Models\Suburb;
use App\Traits\Common_trait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class PropertSeeder extends Seeder
{
    use Common_trait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $limit = 10;
        DB::beginTransaction();
        $propertyQuestions = Question::whereSection(2)->get();
        $housematesQuestions = Question::whereSection(4)->get();
        $roomsQuestions = Question::whereSection(3)->get();

        try {
            $answers = [];
            for ($i = 0; $i < $limit; $i++) {
                $fakeUser = User::inRandomOrder()->first();
                if (!$fakeUser) continue;

                Cache::put('user', $fakeUser);
                // Get some random questions for the property (you can adjust)
                foreach ($propertyQuestions as $key => $question) {
                    $optionsId = QuestionsOption::where("question_id", $question->id)?->pluck('id')->toArray();
                    $answers[$i][]  = [
                        'question_id' => $question->id,
                        'option_id'   => in_array($question->type_for_app, [1, 2]) ? $this->fakeOptionId($question, $optionsId) : null, // or fake()->randomElement([...])
                        'answer'      => in_array($question->type_for_app, [3, 4, 5]) ? $this->fakeAnswer($question) : null,
                    ];
                }

                foreach (range(1, rand(1, 5)) as $hmIndex) {
                    $answers[$i][] = $this->makeHQuestions($housematesQuestions);
                }

                foreach (range(1, rand(1, 15)) as $hmIndex) {
                    $answers[$i][] = $this->makeRQuestions($roomsQuestions);
                }

                $createPropertyOwener = PropertyOwner::updateOrCreate([
                    'user_id' => $fakeUser->id,
                ], [
                    'user_id' => $fakeUser->id,
                ]);

                $property = $createPropertyOwener->properties()->create(
                    [
                        'latitude' => fake()->latitude,
                        'longitude' => fake()->longitude,
                        'status' => true,
                    ]
                );

                $propertyId = $property->id;
                $propertyData = $this->makeQueAns($answers[$i], $fakeUser, $propertyId);
                if (!empty($propertyData)) {
                    // insert property data
                    QuestionAnswerUser::insert($propertyData);
                }

                if (rand(true, false)) {
                    $property->update([
                        'owner_id' => $createPropertyOwener->id,
                    ]);
                }
                Cache::forget('user');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $error = $e->getMessage() . ' - ' . $e->getFile();
            Log::error($error);
        }
    }

    private function fakeAnswer($que)
    {
        if (in_array($que->id, [71, 73, 74])) {
            return rand(100, 9000); // UPDATE `question_answers_user` SET answer = FLOOR(100 + (RAND() * (9000 - 100))) WHERE question_id = 71;
        } else if (in_array($que->id, [72])) {
            return rand(155, 154); // UPDATE `question_answers_user` SET option_id = CASE FLOOR(RAND()*2) WHEN 0 THEN 154 ELSE 155 END WHERE question_id = 72;
        } else if (in_array($que->id, [76])) {
            return rand(156, 163);
        } else if (in_array($que->id, [77])) {
            return rand(164, 171);
        } else if (in_array($que->id, [56])) {
            return rand(88, 93);
        } else if (in_array($que->id, [79])) {
            return rand(177, 182);
        } else if (in_array($que->id, [62])) {
            return rand(110, 114);
        } else if (in_array($que->id, [63])) {
            return rand(115, 120);
        } else if (in_array($que->id, [67])) {
            return rand(129, 131);
        } else if (in_array($que->id, [68])) {
            return rand(132, 134);
        } else if (in_array($que->id, [60])) {
            return rand(104, 106);
        } else if (in_array($que->id, [75])) {
            return $this->randomDate()->format('d/m/Y');
        } else if (in_array($que->id, [57])) {
            $userSelectedSuburb = [1, 2, 3, 4, 40, 44, 899, 907, 910, 1231, 1245, 1472, 1494, 1863, 2020, 2344, 2515, 2532, 2616, 2755, 2841, 2930, 3794, 3874, 4189, 4291, 4517, 4866, 5206, 5585, 6125, 6177, 6316, 6357, 7744, 8808, 8833, 9716, 10103, 10449, 10644, 10726, 10806, 11900, 12031, 12145, 12463, 12524, 12919, 13384, 13876, 14054, 14452, 14470, 14883];
            $suburbQuery = Suburb::where('country', 'Australia')->whereIn('id', $userSelectedSuburb)->inRandomOrder()->first('name');
            return fake()->address() . ',' . $suburbQuery->name;
        } else {
            return match ($que->type_for_app) {
                3 => fake()->sentence(),
                4 => true,
                default => null
            };
        }
    }

    private function fakeOptionId($question, $optionsId)
    {
        if (!$optionsId) {
            return null;
        }
        $key = array_rand($optionsId, 1);
        return  $optionsId[$key];
    }

    private function makeHQuestions($questions)
    {
        $array = [];
        foreach ($questions as $k => $hQuestion) {
            $hOptionsId = QuestionsOption::where("question_id", $hQuestion->id)?->pluck('id')->toArray() ?? [];
            $array['housemate'][$k] = [
                'question_id' => $hQuestion->id,
                'option_id'   => in_array($hQuestion->type_for_app, [1, 2]) ? $this->fakeOptionId($hQuestion, $hOptionsId) : null, // or fake()->randomElement([...])
                'answer'      => in_array($hQuestion->type_for_app, [3, 4, 5]) ? $this->fakeAnswer($hQuestion) : null,
                'file'        => in_array($hQuestion->id, [92]) ? fake()->imageUrl(800, 600, 'house', true) : null,
            ];
        }
        return $array;
    }

    private function makeRQuestions($questions)
    {
        $array = [];
        foreach ($questions as $k => $hQuestion) {
            $hOptionsId = QuestionsOption::where("question_id", $hQuestion->id)?->pluck('id')->toArray() ?? [];
            $array['rooms'][$k] = [
                'question_id' => $hQuestion->id,
                'option_id'   => in_array($hQuestion->type_for_app, [1, 2]) ? $this->fakeOptionId($hQuestion, $hOptionsId) : null, // or fake()->randomElement([...])
                'answer'      => in_array($hQuestion->type_for_app, [3, 4, 5]) ? $this->fakeAnswer($hQuestion) : null,
                'file'        => in_array($hQuestion->id, [86]) ? [fake()->imageUrl(800, 600, 'house', true)] : null,
            ];
        }
        return $array;
    }

    private function randomDate()
    {
        $year = date('Y');
        $min = strtotime("$year-01-01");
        $max = strtotime("$year-12-31");
        $val = rand($min, $max);
        return new \DateTime(date('Y-m-d', $val));
    }
}
