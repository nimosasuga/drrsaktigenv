<?php
// PATH FILE: app/Support/ReminderCounter.php

namespace App\Support;

use App\Models\Job;
use App\Models\SparepartRecommendationControl;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReminderCounter
{
    private const OLD_PROBLEM_DAYS = 7;

    public static function countForUser(?User $user): int
    {
        if (!$user) {
            return 0;
        }

        $ids = collect();

        foreach (self::types() as $type) {
            $ids = $ids->merge(
                self::applyType(self::baseQuery($user), $type)->pluck('id')
            );
        }

        return $ids->unique()->count();
    }

    private static function types(): array
    {
        return [
            'waiting_part',
            'breakdown',
            'rfu_empty',
            'problem_old',
            'recommendation_pending',
        ];
    }

    private static function baseQuery(User $user): Builder
    {
        $query = Job::query();
        $role = (string) ($user->status_user ?? '');

        if (!in_array($role, ['koordinator', 'sect_head', 'admin', 'super_admin'], true)) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private static function applyType(Builder $query, string $type): Builder
    {
        return match ($type) {
            'waiting_part' => $query->where('status_unit', 'Waiting Part'),
            'breakdown' => $query->where('status_unit', 'Breakdown'),
            'rfu_empty' => $query
                ->where(function (Builder $q) {
                    $q->whereNull('rfu_date')->orWhere('rfu_date', '');
                })
                ->where(function (Builder $q) {
                    $q->whereNull('status_unit')->orWhere('status_unit', '!=', 'RFU');
                }),
            'problem_old' => $query
                ->whereNotNull('problem_date')
                ->whereDate('problem_date', '<=', Carbon::today()->subDays(self::OLD_PROBLEM_DAYS))
                ->where(function (Builder $q) {
                    $q->whereNull('status_unit')->orWhere('status_unit', '!=', 'RFU');
                }),
            'recommendation_pending' => $query->whereHas('recommendations', function (Builder $recommendationQuery) {
                $recommendationQuery->where(function (Builder $q) {
                    $q->whereNotExists(function ($subQuery) {
                        $subQuery
                            ->select(DB::raw(1))
                            ->from('sparepart_recommendation_controls')
                            ->whereColumn('sparepart_recommendation_controls.job_recommendation_id', 'job_recommendations.id');
                    })->orWhereExists(function ($subQuery) {
                        $subQuery
                            ->select(DB::raw(1))
                            ->from('sparepart_recommendation_controls')
                            ->whereColumn('sparepart_recommendation_controls.job_recommendation_id', 'job_recommendations.id')
                            ->whereNotIn('sparepart_recommendation_controls.recommendation_status', [
                                SparepartRecommendationControl::STATUS_REJECTED,
                                SparepartRecommendationControl::STATUS_INSTALLED,
                                SparepartRecommendationControl::STATUS_CLOSED,
                                SparepartRecommendationControl::STATUS_CANCELLED,
                            ]);
                    });
                });
            }),
            default => $query,
        };
    }
}
