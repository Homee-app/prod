<?php

use App\Constants\QuestionConstants;
use App\Models\Question;
use App\Models\QuestionAnswerUser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;


if (!function_exists('pre')) {
    function pre($data = '', $status = FALSE)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        if (!$status) {
            die;
        }
    }
}

// For API
if (!function_exists('pree')) {
    function pree($data = '', $status = FALSE)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        if (!$status) {
            die;
        }
    }
}

if (!function_exists('getDateInFormat')) {
    function getDateInFormat($date)
    {
        if (!empty($date)) {
            $dateTimeObject = new DateTime($date);
            return $formattedDateTime = $dateTimeObject->format('d M, Y');
        } else {
            return '-';
        }
    }
}


if (!function_exists('get_avatar')) {
    function get_avatar($avatar = '')
    {
        return $avatar == '' ? asset('assets/img/user.png') : asset($avatar);
    }
}



if (!function_exists('deleteFromS3')) {
    function deleteFromS3($filePath)
    {
        if (!$filePath) {
            return false;
        }

        try {
            return Storage::disk('s3')->delete($filePath);
        } catch (\Exception $e) {
            app('log')->error('AWS S3 Deletion Error: ' . $e->getMessage());
            return false;
        }
    }
}


if (!function_exists('percentageCalculator')) {
    /**
     * Calculate percentage with two decimal precision.
     *
     * @param int|float $total
     * @param int|float $answeredQuestions
     * @return float
     */
    function percentageCalculator($total, $item): float
    {
        return $total > 0
            ? round(($item / $total) * 100, 2)
            : 0.0;
    }
}

if (!function_exists('p')) {
    function p($item)
    {
        echo ('<pre>');
        print_r($item);
        echo ('</pre>');
    }
}

if (!function_exists('pd')) {
    function pd(...$items)
    {
        foreach ($items as $item) {
            p($item);
        }
        dd('');
    }
}

if (!function_exists('makeAnswerSting')) {
    function makeAnswerSting($questions, $test = null)
    {
        $value = [];
        foreach ($questions as $question) {
            $selectedOptionAns = $question->userAnswer->first()?->answer ?? null;
            if (!$selectedOptionAns) {
                $selectedOptionId = $question->userAnswer->first()?->option_id;
                $selected = $question?->options->firstWhere('id', $selectedOptionId);
                $label = $selected?->label_for_app ?? '';
                $val = checkString($question->id, $label);
                if ($val !== '') {
                    $value[] = $val;
                }
            } else {
                $val = checkString($question->id, $selectedOptionAns);
                if ($val !== '') {
                    $value[] = $val;
                }
            }
        };

        return implode(', ', $value);
    }
}

if (!function_exists('checkString')) {
    function checkString($que, $val)
    {

        $prefixStringArray = [
            71 => '$',
            73 => '$',
            74 => '$',
        ];

        $suffixStringArray = [
            58 => 'Bedrooms',
            59 => 'Bathrooms',
            64 => 'Homees',
            73 => 'Approximate Cost',
            74 => 'Bond',
        ];

        if ($que === 72) {
            $val = ($val == 'Yes') ? 'bills included ' : 'bills not included';
        }

        if ($que === 75) {
            $val = \Carbon\Carbon::createFromFormat('d/m/Y', $val)->format('j F Y');
        }

        $prefix = $prefixStringArray[$que] ?? '';
        $suffix = $suffixStringArray[$que] ?? '';
        return trim("{$prefix}{$val} {$suffix}");
    }
}

if (!function_exists('hasRelation')) {
    function hasRelation($model, string $relation): bool
    {
        if (is_string($model)) {
            if (!class_exists($model)) {
                return false;
            }
            $model = new $model;
        }

        if (!$model instanceof Model) {
            return false;
        }

        // Get all relation methods
        return method_exists($model, $relation) && $model->$relation() instanceof \Illuminate\Database\Eloquent\Relations\Relation;
    }
}

if (!function_exists('extractSuburbFromAddress')) {
    function extractSuburbFromAddress(string $address): ?string
    {
        // Split by commas first
        $parts = array_map('trim', explode(',', $address));

        // Look through parts for the one that contains the state code (QLD, NSW, VIC, etc.)
        $stateCodes = ['ACT', 'NSW', 'NT', 'QLD', 'SA', 'TAS', 'VIC', 'WA'];

        foreach ($parts as $part) {
            foreach ($stateCodes as $code) {
                if (stripos($part, " $code") !== false) {
                    // Remove state code, keep only suburb name
                    $suburb = trim(str_ireplace($code, '', $part));
                    return $suburb ?: null;
                }
            }
        }

        return null; // no suburb found
    }
}

if (!function_exists('getStartEndTimeByMinutes')) {
    function getStartEndTimeByMinutes(int $minutes, string $formate = 'Y-m-d H:i:s'): array
    {
        $start = now()->subMinutes($minutes);
        $end = now();

        return [
            'startTime' => $start->format($formate),
            'endTime'   => $end->format($formate),
        ];
    }
}

if (!function_exists('make_transaction_date')) {
    function make_transaction_date(string $date, $formate = 'Y-m-d H:i:s'): string
    {
        if (is_numeric($date)) {
            // Handle milliseconds timestamp
            if (strlen($date) > 10) {
                // Milliseconds → convert to seconds
                $date = intval($date) / 1000;
            }
            return date($formate, $date);
        } else {
            // Already a date string → just normalize
            return date($formate, strtotime($date));
        }
    }
}

if (!function_exists('is_production')) {
    function is_production()
    {
        $env = config('app.env');
        return $env === 'production';
    }
}

if (!function_exists('findUserPercentage')) {
    function findUserPercentage($userId)
    {
        $profileQuestionsFor = QuestionConstants::SESSION_QUESTIONS_FOR['profile'] ?? [];
        $totalQuestionsId = Question::where('section', 1)->whereIn('question_for', $profileQuestionsFor)->pluck('id');

        // 2. Get number of distinct question_ids this user has answered
        $answeredQuestions = QuestionAnswerUser::where('user_id', $userId)
            ->whereIn('question_id', $totalQuestionsId)
            ->whereNull('deleted_at') // if soft deletes are used
            ->distinct('question_id')
            ->count('question_id');

        // 3. Calculate percentage by helper function
        $completionPercentage = percentageCalculator(count($totalQuestionsId), $answeredQuestions);

        return [
            'total_questions' => count($totalQuestionsId),
            'answered_questions' => $answeredQuestions,
            'completion_percentage' => $completionPercentage,
            'is_completed' => $completionPercentage == 100 ? true : false
        ];
    }
}
