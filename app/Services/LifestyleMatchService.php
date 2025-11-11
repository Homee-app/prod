<?php

namespace App\Services;

use App\Constants\QuestionConstants;
use Illuminate\Support\Facades\Config;

class LifestyleMatchService
{
    protected array $lifestyleQuestionIds;
    protected array $columnMap;
    protected array $currentUserAnswers;

    public function __construct($currentProfile)
    {
        $this->lifestyleQuestionIds = QuestionConstants::SESSION_QUESTIONS_FOR['lifestyle_question_ids'];
        $this->columnMap = Config::get('lifestyle.column_map', []);

        // Collect current user’s answers mapped to lifestyle question ids
        $this->currentUserAnswers = collect($this->lifestyleQuestionIds)->mapWithKeys(function ($id) use ($currentProfile) {
            return [$id => (int) optional($currentProfile)->{$this->columnMap[$id] ?? null}];
        })->toArray();
    }

    /**
     * Build SQL parts (percentExpr, bindings) for use in query scopes
     */
    public function buildSqlParts(): array
    {
        $matchParts = [];
        $bindings = [];

        foreach ($this->currentUserAnswers as $questionId => $answerValue) {
            $columnName = $this->columnMap[$questionId] ?? null;

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

        return [
            'percentExpr' => $percentExpr,
            'bindings' => $bindings,
            'matchCount' => $matchCount,
        ];
    }

    /**
     * Calculate lifestyle match percent in PHP for a single user profile
     */
    public function calculatePhpMatch(array $otherProfileAnswers): int
    {
        $matchValues = [];
        foreach ($this->currentUserAnswers as $qId => $answerValue) {
            $columnName = $this->columnMap[$qId] ?? null;
            $otherValue = $otherProfileAnswers[$qId] ?? null;

            if ($answerValue !== null && $otherValue !== null && $columnName) {
                $matchValues[] = 1 - abs(($otherValue / 10.0) - ($answerValue / 10.0));
            }
        }

        return count($matchValues) > 0
            ? round((array_sum($matchValues) / count($matchValues)) * 100, 0)
            : 0;
    }
}
