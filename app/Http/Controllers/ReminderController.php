<?php
// PATH FILE: app/Http/Controllers/ReminderController.php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\SparepartRecommendationControl;
use App\Models\User;
use App\Support\DepartmentScope;
use App\Support\ReminderCounter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReminderController extends Controller
{
    private const OLD_PROBLEM_DAYS = 7;

    public function index(Request $request)
    {
        $user = $request->user();
        $role = (string) ($user->status_user ?? '');
        $canSeeAllDepartments = DepartmentScope::userCanSeeAllDepartments($user);
        $canSeeTeam = in_array($role, ['koordinator', 'sect_head', 'admin', 'super_admin'], true);

        $selectedDepartment = strtoupper(trim((string) $request->query('department', '')));
        if (!$canSeeAllDepartments) {
            $selectedDepartment = DepartmentScope::currentDepartment($user) ?: '';
        }

        $users = $this->accessibleUsers($user, $selectedDepartment)->get();
        $allowedUserIds = $users->pluck('id')->map(fn ($id) => (int) $id)->all();

        $selectedUserId = (int) $request->query('user_id', 0);
        if ($selectedUserId > 0 && !in_array($selectedUserId, $allowedUserIds, true)) {
            $selectedUserId = 0;
        }

        if (!$canSeeTeam) {
            $selectedUserId = (int) $user->id;
        }

        $typeOptions = $this->typeOptions();
        $selectedType = (string) $request->query('type', 'all');
        if ($selectedType !== 'all' && !array_key_exists($selectedType, $typeOptions)) {
            $selectedType = 'all';
        }

        $groups = [];
        foreach ($typeOptions as $key => $meta) {
            if ($selectedType !== 'all' && $selectedType !== $key) {
                continue;
            }

            $countQuery = $this->applyReminderType(
                $this->baseJobQuery($user, $selectedUserId, $selectedDepartment),
                $key
            );

            $itemQuery = $this->applyReminderType(
                $this->baseJobQuery($user, $selectedUserId, $selectedDepartment),
                $key
            )
                ->with(['user', 'recommendations'])
                ->orderByDesc('work_date')
                ->orderByDesc('id')
                ->limit(50);

            $groups[$key] = array_merge($meta, [
                'count' => $countQuery->count(),
                'jobs' => $itemQuery->get(),
            ]);
        }

        $totalUniqueJobs = $this->totalUniqueJobs($user, $selectedUserId, $selectedDepartment);
        $departmentOptions = $canSeeAllDepartments ? ['RENTAL', 'SERVICE'] : array_filter([$selectedDepartment]);

        return view('reminders.index', [
            'groups' => $groups,
            'typeOptions' => $typeOptions,
            'selectedType' => $selectedType,
            'users' => $users,
            'selectedUserId' => $selectedUserId,
            'canSeeTeam' => $canSeeTeam,
            'canSeeAllDepartments' => $canSeeAllDepartments,
            'departmentOptions' => $departmentOptions,
            'selectedDepartment' => $selectedDepartment,
            'scopeLabel' => $this->scopeLabel($user, $selectedUserId, $users, $selectedDepartment),
            'totalUniqueJobs' => $totalUniqueJobs,
            'oldProblemDays' => self::OLD_PROBLEM_DAYS,
        ]);
    }

    public function count(Request $request): JsonResponse
    {
        return response()->json([
            'count' => ReminderCounter::countForUser($request->user()),
        ]);
    }

    private function accessibleUsers(User $user, string $selectedDepartment): Builder
    {
        $query = User::query()
            ->select(['id', 'name', 'nrpp', 'status_user', 'department', 'branch'])
            ->orderBy('department')
            ->orderBy('status_user')
            ->orderBy('name');

        if (DepartmentScope::userCanSeeAllDepartments($user)) {
            if ($selectedDepartment !== '') {
                $query->where('department', $selectedDepartment);
            }

            return $query;
        }

        if (in_array((string) $user->status_user, ['koordinator', 'sect_head'], true)) {
            $department = DepartmentScope::currentDepartment($user);

            if (!$department) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where('department', $department);
        }

        return $query->whereKey($user->id);
    }

    private function baseJobQuery(User $user, int $selectedUserId, string $selectedDepartment): Builder
    {
        $query = Job::query();
        $role = (string) ($user->status_user ?? '');

        if (DepartmentScope::userCanSeeAllDepartments($user) && $selectedDepartment !== '') {
            $query->where('department', $selectedDepartment);
        }

        if (!in_array($role, ['koordinator', 'sect_head', 'admin', 'super_admin'], true)) {
            return $query->where('user_id', $user->id);
        }

        if ($selectedUserId > 0) {
            $query->where('user_id', $selectedUserId);
        }

        return $query;
    }

    private function typeOptions(): array
    {
        return [
            'waiting_part' => [
                'label' => 'Waiting Part',
                'short_label' => 'Part',
                'description' => 'Unit menunggu sparepart atau material sebelum pekerjaan bisa diselesaikan.',
                'priority' => 'Tinggi',
            ],
            'breakdown' => [
                'label' => 'Breakdown',
                'short_label' => 'BD',
                'description' => 'Unit berstatus breakdown dan perlu perhatian lebih cepat.',
                'priority' => 'Tinggi',
            ],
            'rfu_empty' => [
                'label' => 'RFU Date Kosong',
                'short_label' => 'RFU',
                'description' => 'Pekerjaan belum memiliki tanggal RFU, sehingga perlu dikonfirmasi ulang.',
                'priority' => 'Sedang',
            ],
            'problem_old' => [
                'label' => 'Problem Date Lama',
                'short_label' => 'Lama',
                'description' => 'Problem date sudah melewati batas pemantauan dan belum selesai.',
                'priority' => 'Sedang',
            ],
            'recommendation_pending' => [
                'label' => 'Recommendation Belum Action',
                'short_label' => 'Rec',
                'description' => 'Ada rekomendasi sparepart yang belum ditutup, belum rejected, atau belum installed.',
                'priority' => 'Sedang',
            ],
        ];
    }

    private function applyReminderType(Builder $query, string $type): Builder
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

    private function totalUniqueJobs(User $user, int $selectedUserId, string $selectedDepartment): int
    {
        $ids = collect();

        foreach (array_keys($this->typeOptions()) as $type) {
            $ids = $ids->merge(
                $this->applyReminderType($this->baseJobQuery($user, $selectedUserId, $selectedDepartment), $type)
                    ->pluck('id')
            );
        }

        return $ids->unique()->count();
    }

    private function scopeLabel(User $user, int $selectedUserId, $users, string $selectedDepartment): string
    {
        if ($selectedUserId > 0) {
            $selectedUser = $users->firstWhere('id', $selectedUserId);

            if ($selectedUser) {
                return 'User: ' . $selectedUser->name;
            }
        }

        if (DepartmentScope::userCanSeeAllDepartments($user)) {
            return $selectedDepartment !== '' ? 'Department: ' . $selectedDepartment : 'Semua department';
        }

        if (in_array((string) $user->status_user, ['koordinator', 'sect_head'], true)) {
            return 'Department: ' . ($selectedDepartment ?: '-');
        }

        return 'Pengingat pribadi';
    }
}
