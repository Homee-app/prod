<?php

namespace App\QueryBuilders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NearbyTenantQuery
{
    protected Builder $query;
    protected $currentUser;
    protected array $filters;
    protected array $currentUserAnswers;

    public function __construct(Builder $query, $currentUser, array $filters = [], array $currentUserAnswers = [])
    {
        $this->query = $query;
        $this->currentUser = $currentUser;
        $this->filters = $filters;
        $this->currentUserAnswers = $currentUserAnswers;
    }

    public function build(): Builder
    {
        $userLatitude = $this->currentUser->latitude ?? 0;
        $userLongitude = $this->currentUser->longitude ?? 0;
        $earthRadius = 6371;

        $filters = $this->filters;

        if (empty($filters['suburb_ids'])) {
            $filters['suburb_ids'] = DB::table('question_answers_user')
                ->where('user_id', $this->currentUser->id)
                ->where('question_id', 20)
                ->pluck('option_id')
                ->toArray();
        }

        $this->query->join('tenant_profiles', 'users.id', '=', 'tenant_profiles.user_id')
            ->where('tenant_profiles.is_teamup', true);

        if (!empty($filters['show_verified_only'])) {
            $this->query->join('user_identities as uid', function ($join) {
                $join->on('users.id', '=', 'uid.user_id')
                    ->where('uid.verification_status', 'approved');
            });
        }

        // Single join to question_answers_user for HAVING filtering
        if (!empty($this->filters)) {
            $this->query->join('question_answers_user as qau', 'users.id', '=', 'qau.user_id');
        }

        // === ADD BUDGET FILTER JOIN AND WHERE CLAUSES HERE ===
        if (($filters['min_budget'] ?? null) !== null || ($filters['max_budget'] ?? null) !== null) {
            $minBudget = $filters['min_budget'] ?? null;
            $maxBudget = $filters['max_budget'] ?? null;

            $this->query->join('question_answers_user as budget_qau', function ($join) {
                $join->on('users.id', '=', 'budget_qau.user_id')
                    ->where('budget_qau.question_id', 23);
            });

            if ($minBudget !== null) {
                $this->query->whereRaw("CAST(SUBSTRING_INDEX(budget_qau.answer, ',', 1) AS UNSIGNED) >= ?", [$minBudget]);
            }

            if ($maxBudget !== null) {
                $this->query->whereRaw("CAST(SUBSTRING_INDEX(budget_qau.answer, ',', -1) AS UNSIGNED) <= ?", [$maxBudget]);
            }
        }

        $matchParts = [];
        $bindings = [];
        $selectParts = [];

        if ($userLongitude != 0 && $userLatitude != 0) {
            $bindings[] = $userLatitude;
            $bindings[] = $userLongitude;
            $bindings[] = $userLatitude;

            $selectParts[] = "({$earthRadius} * acos(
                cos(radians(?)) * cos(radians(users.latitude)) *
                cos(radians(users.longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(users.latitude))
            )) as distance";
        } else {
            $selectParts[] = "0 as distance";
        }

        $columnMap = config('lifestyle.column_map');

        foreach ($this->currentUserAnswers as $questionId => $answerValue) {
            $columnName = $columnMap[$questionId] ?? null;

            if ($answerValue !== null && $columnName) {
                $normalized = round($answerValue / 10, 2);
                $matchParts[] = "(1 - ABS((IFNULL(tenant_profiles.{$columnName}, 0) / 10.0) - ?))";
                $bindings[] = $normalized;
            }
        }

        $matchCount = count($matchParts);
        $percentExpr = $matchCount > 0
            ? "ROUND(((" . implode(' + ', $matchParts) . ") / {$matchCount}) * 100, 0)"
            : '0';

        $selectParts[] = "users.id";
        $selectParts[] = "users.email";
        $selectParts[] = "users.profile_photo";
        $selectParts[] = "users.partner_profile_photo";
        $selectParts[] = "tenant_profiles.is_teamup";
        // Subqueries for dob and availability_date instead of joins
        $selectParts[] = "(
                SELECT STR_TO_DATE(answer, '%d/%m/%Y')
                FROM question_answers_user
                WHERE question_id = 3 AND user_id = users.id AND for_partner = false
                ORDER BY id DESC
                LIMIT 1
            ) as dob";
        $selectParts[] = "(
                SELECT STR_TO_DATE(answer, '%d/%m/%Y')
                FROM question_answers_user
                WHERE question_id = 22 AND user_id = users.id
                ORDER BY id DESC
                LIMIT 1
            ) as availability_date";
        $selectParts[] = "users.latitude";
        $selectParts[] = "users.longitude";
        $selectParts[] = "{$percentExpr} as lifestyle_match_percent";
        $selectParts[] = "CASE WHEN EXISTS (
            SELECT 1 FROM " . Config('tables.tenant_likes_list') . " 
            WHERE " . Config('tables.tenant_likes_list') . ".user_id = {$this->currentUser->id} 
            AND tenant_likes.tenant_id = users.id
            ) THEN TRUE ELSE FALSE END as is_saves";
        $selectParts[] = "CASE WHEN EXISTS (
                    SELECT 1 
                    FROM " . config('tables.user_boost_usages') . " ubu
                    WHERE ubu.user_id = users.id
                    AND ubu.used_at > NOW() - INTERVAL 24 HOUR
                ) THEN TRUE ELSE FALSE END as is_boost";

        $selectParts[] = "(
                    SELECT MAX(ub.used_at)
                    FROM " . config('tables.user_boost_usages') . " ub
                    WHERE ub.user_id = users.id
                    AND ub.used_at > NOW() - INTERVAL 24 HOUR
                ) as boost_used_at";

        $this->query->selectRaw(implode(', ', $selectParts), $bindings);

        $this->query->where('users.id', '!=', $this->currentUser->id)
            ->where('users.role', 2)
            ->whereNull('users.deleted_at')
        ;

        // Filters on availability_date using correlated subquery

        if (!empty($filters['availability_date'])) {
            $originalDate = $filters['availability_date']; // Format: '15/07/2025'

            // Ensure it's a valid date
            try {
                $inputDate = Carbon::createFromFormat('d/m/Y', $originalDate)->format('d/m/Y');

                $this->query->whereRaw("
                    (
                        SELECT STR_TO_DATE(TRIM(answer), '%d/%m/%Y')
                        FROM question_answers_user
                        WHERE question_id = 22 AND user_id = users.id
                        ORDER BY id DESC
                        LIMIT 1
                    ) <= STR_TO_DATE(?, '%d/%m/%Y')
                ", [$inputDate]);
            } catch (\Exception $e) {
                // Optionally log or handle invalid date
            }
        }

        // Filters on age using correlated subquery on dob
        if (!empty($filters['min_age']) || !empty($filters['max_age'])) {
            $minDate = !empty($filters['max_age']) ? Carbon::now()->subYears($filters['max_age'])->format('Y-m-d') : null;
            $maxDate = !empty($filters['min_age']) ? Carbon::now()->subYears($filters['min_age'])->format('Y-m-d') : null;

            if ($minDate && $maxDate) {
                $this->query->whereRaw("
                    (
                        SELECT STR_TO_DATE(answer, '%d/%m/%Y')
                        FROM question_answers_user
                        WHERE question_id = 3 AND user_id = users.id AND for_partner = false
                        ORDER BY id DESC
                        LIMIT 1
                    ) BETWEEN ? AND ?
                ", [$minDate, $maxDate]);
            } elseif ($minDate) {
                $this->query->whereRaw("
                    (
                        SELECT STR_TO_DATE(answer, '%d/%m/%Y')
                        FROM question_answers_user
                        WHERE question_id = 3 AND user_id = users.id AND for_partner = false
                        ORDER BY id DESC
                        LIMIT 1
                    ) >= ?
                ", [$minDate]);
            } elseif ($maxDate) {
                $this->query->whereRaw("
                    (
                        SELECT STR_TO_DATE(answer, '%d/%m/%Y')
                        FROM question_answers_user
                        WHERE question_id = 3 AND user_id = users.id AND for_partner = false
                        ORDER BY id DESC
                        LIMIT 1
                    ) <= ?
                ", [$maxDate]);
            }
        }

        $this->query->having('distance', '<=', $filters['max_distance'] ?? 50);

        if (!empty($filters['min_lifestyle_match_percent']) && $matchCount > 0) {
            $this->query->having('lifestyle_match_percent', '>=', $filters['min_lifestyle_match_percent']);
        }

        // Build groupBy list
        $groupByColumns = [
            'users.id',
            'users.email',
            'users.profile_photo',
            'users.partner_profile_photo',
            'tenant_profiles.is_teamup',
            'users.latitude',
            'users.longitude',
            // 'users.is_boost'
        ];

        foreach ($this->currentUserAnswers as $questionId => $answerValue) {
            $columnName = $columnMap[$questionId] ?? null;
            if ($columnName && !in_array("tenant_profiles.{$columnName}", $groupByColumns)) {
                $groupByColumns[] = "tenant_profiles.{$columnName}";
            }
        }

        $this->query->groupBy(...$groupByColumns);

        $this->applyHavingConditions();

        $this->query
        ->orderByDesc('boost_used_at')
        ->orderBy('distance');

        if (request('data_query')) {
            dd($this->getInterpolatedQuery($this->query));
        }
        return $this->query;
    }


    /**
     * Apply having conditions based on filters.
     */

    protected function applyHavingConditions(): void
    {
        $filters = $this->filters;
        if (empty($this->filters)) {
            // If no filters are set, skip applying any having conditions
            return;
        }
        $map = [
            20 => 'suburb_ids',
            4  => 'gender_option_ids',
            21 => 'stay_length_option_id',
            9  => 'dietary_option_ids',
            30 => 'interest_option_ids',
            5  => 'sexuality_option_ids',
            31 => 'religion_option_ids',
            18 => 'ethnicity_option_ids',
            26 => 'language_option_ids',
            27 => 'political_view_option_ids',
            6 => 'employment_status_option_ids',
            //28 => 'open_to_guests',
            // 24 => 'has_rental_history',
        ];

        $conditions = [];

        foreach ($map as $questionId => $filterKey) {
            if (!empty($filters[$filterKey])) {
                $ids = is_array($filters[$filterKey])
                    ? implode(',', array_map('intval', $filters[$filterKey]))
                    : intval($filters[$filterKey]);
                $conditions[] = "SUM(CASE WHEN qau.question_id = $questionId AND qau.option_id IN ($ids) AND qau.deleted_at IS NULL THEN 1 ELSE 0 END) > 0";
            }
        }

        if (!empty($filters['open_to_guests'])) {
            //echo "Open to guests filter is not implemented in the query"; exit;
            $conditions[] = "SUM(CASE WHEN qau.question_id = 28 AND qau.option_id IN (63,65) AND qau.deleted_at IS NULL THEN 1 ELSE 0 END) > 0";
        }

        if (!empty($filters['has_rental_history'])) {
            $conditions[] = "SUM(CASE WHEN qau.question_id = 24 AND qau.option_id = 53 AND qau.deleted_at IS NULL THEN 1 ELSE 0 END) > 0";
        }

        if (!empty($filters['prefers_non_drinker'])) {
            $conditions[] = "SUM(CASE WHEN qau.question_id = 7 AND qau.option_id = 27 AND qau.deleted_at IS NULL THEN 1 ELSE 0 END) > 0";
        }

        if (!empty($filters['prefers_non_smoker'])) {
            $conditions[] = "SUM(CASE WHEN qau.question_id = 8 AND qau.option_id = 30 AND qau.deleted_at IS NULL THEN 1 ELSE 0 END) > 0";
        }

        if (isset($filters['open_to_pets'])) {
            if ($filters['open_to_pets']) {
                $conditions[] = "SUM(CASE WHEN qau.question_id = 29 AND qau.option_id IN (66,67) AND qau.deleted_at IS NULL THEN 1 ELSE 0 END) > 0";
            } else {
                $conditions[] = "SUM(CASE WHEN qau.question_id = 29 AND qau.option_id = 68 AND qau.deleted_at IS NULL THEN 1 ELSE 0 END) > 0";
            }
        }
        //pree($conditions);
        if (!empty($conditions)) {
            $this->query->havingRaw(implode(' AND ', $conditions));
        }
    }

    public function getInterpolatedQuery($query)
    {
        $sql = $query->toSql();
        foreach ($query->getBindings() as $binding) {
            $sql = preg_replace('/\?/', is_numeric($binding) ? $binding : "'$binding'", $sql, 1);
        }
        return $sql;
    }
}
