<?php
// PATH FILE: app/Support/ReminderSummaryService.php

namespace App\Support;

use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Collection;

class ReminderSummaryService
{
    public static function countForUser(?User $user): int
    {
        if (!$user) {
            return 0;
        }

        return self::baseQueryForUser($user)->count();
    }

    public static function summaryForUser(?User $user): array
    {
        if (!$user) {
            return [
                'total' => 0,
                'urgent' => 0,
                'high' => 0,
                'normal' => 0,
                'breakdown' => 0,
                'waiting_part' => 0,
                'monitoring' => 0,
                'overdue' => 0,
            ];
        }

        return [
            'total' => self::countForUser($user),
            'urgent' => self::baseQueryForUser($user)
                ->where(function ($query) {
                    $query->where('status_unit', 'Breakdown')
                        ->orWhere(function ($nested) {
                            $nested->whereNotNull('problem_date')
                                ->whereNull('rfu_date')
                                ->where('problem_date', '<=', now()->subDays(3)->toDateString());
                        });
                })
                ->count(),
            'high' => self::baseQueryForUser($user)
                ->where('status_unit', 'Waiting Part')
                ->count(),
            'normal' => self::baseQueryForUser($user)
                ->where('status_unit', 'Monitoring')
                ->count(),
            'breakdown' => self::baseQueryForUser($user)
                ->where('status_unit', 'Breakdown')
                ->count(),
            'waiting_part' => self::baseQueryForUser($user)
                ->where('status_unit', 'Waiting Part')
                ->count(),
            'monitoring' => self::baseQueryForUser($user)
                ->where('status_unit', 'Monitoring')
                ->count(),
            'overdue' => self::baseQueryForUser($user)
                ->whereNotNull('problem_date')
                ->whereNull('rfu_date')
                ->where('problem_date', '<=', now()->subDays(3)->toDateString())
                ->count(),
        ];
    }

    public static function listForUser(?User $user, ?string $filter = null, int $limit = 50): Collection
    {
        if (!$user) {
            return collect();
        }

        $filter = strtolower(trim((string) $filter));
        $query = self::baseQueryForUser($user)
            ->with('user')
            ->latest('updated_at')
            ->limit($limit);

        if ($filter === 'breakdown') {
            $query->where('status_unit', 'Breakdown');
        } elseif ($filter === 'waiting_part') {
            $query->where('status_unit', 'Waiting Part');
        } elseif ($filter === 'monitoring') {
            $query->where('status_unit', 'Monitoring');
        } elseif ($filter === 'overdue') {
            $query->whereNotNull('problem_date')
                ->whereNull('rfu_date')
                ->where('problem_date', '<=', now()->subDays(3)->toDateString());
        }

        return $query->get()->map(function (Job $job) {
            return [
                'id' => $job->id,
                'title' => self::titleForJob($job),
                'priority' => self::priorityForJob($job),
                'status_unit' => $job->status_unit ?: '-',
                'serial_number' => $job->serial_number ?: '-',
                'customer' => $job->customer ?: '-',
                'location' => $job->location ?: '-',
                'pic' => $job->pic ?: ($job->user->name ?? '-'),
                'department' => $job->department ?: '-',
                'work_date' => $job->work_date,
                'problem_date' => $job->problem_date,
                'updated_at' => $job->updated_at,
                'url' => route('update-jobs.show', $job),
            ];
        });
    }

    private static function baseQueryForUser(User $user)
    {
        $query = Job::query()
            ->where(function ($query) {
                $query->whereIn('status_unit', ['Breakdown', 'Waiting Part', 'Monitoring'])
                    ->orWhere(function ($nested) {
                        $nested->whereNotNull('problem_date')
                            ->whereNull('rfu_date')
                            ->where('problem_date', '<=', now()->subDays(3)->toDateString());
                    });
            });

        if (self::normalizedStatusUser($user) === 'mekanik') {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private static function titleForJob(Job $job): string
    {
        if ($job->status_unit === 'Breakdown') {
            return 'Breakdown belum RFU';
        }

        if ($job->status_unit === 'Waiting Part') {
            return 'Waiting Part perlu follow-up';
        }

        if ($job->status_unit === 'Monitoring') {
            return 'Monitoring perlu dicek';
        }

        return 'Problem lama belum RFU';
    }

    private static function priorityForJob(Job $job): string
    {
        if ($job->status_unit === 'Breakdown') {
            return 'urgent';
        }

        if ($job->problem_date && !$job->rfu_date && $job->problem_date->lte(now()->subDays(3))) {
            return 'urgent';
        }

        if ($job->status_unit === 'Waiting Part') {
            return 'high';
        }

        return 'normal';
    }

    private static function normalizedStatusUser(User $user): string
    {
        $statusUser = strtolower(trim((string) $user->status_user));
        $statusUser = str_replace(['-', ' '], '_', $statusUser);

        if ($statusUser === 'secthead') {
            return 'sect_head';
        }

        if ($statusUser === 'superadmin') {
            return 'super_admin';
        }

        return $statusUser;
    }
}
