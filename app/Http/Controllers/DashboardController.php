<?php

namespace App\Http\Controllers;

use App\Models\TaskCategory;
use App\Models\TaskMaster;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $defaultStartDate = now()->subDays(6)->startOfDay();
        $defaultEndDate = now()->endOfDay();
        $plannedBy = (int) $request->input('planned_by', 0);
        $taskCategoryId = (int) $request->input('task_category_id', 0);
        $user = $request->user();

        $startDate = $this->parseDate($request->input('start_date'), $defaultStartDate)->startOfDay();
        $endDate = $this->parseDate($request->input('end_date'), $defaultEndDate)->endOfDay();

        $adminUsers = User::query()
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', 'administrator');
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($user && $adminUsers->pluck('id')->contains((int) $user->id)) {
            $plannedBy = (int) $user->id;
        }

        if ($plannedBy > 0 && ! $adminUsers->pluck('id')->contains($plannedBy)) {
            $plannedBy = $user ? (int) $user->id : 0;
        }

        if ($startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $tasks = TaskMaster::query()
            ->with('category')
            ->where(function (Builder $query) use ($startDate, $endDate) {
                $this->applyDateOverlap($query, 'date_planning_start', 'date_planning_finish', $startDate, $endDate);
                $query->orWhere(function (Builder $nested) use ($startDate, $endDate) {
                    $this->applyDateOverlap($nested, 'date_realization_start', 'date_realization_finish', $startDate, $endDate);
                });
            })
            ->when($plannedBy > 0, function (Builder $query) use ($plannedBy) {
                $query->where('planned_by', $plannedBy);
            })
            ->when($taskCategoryId > 0, function (Builder $query) use ($taskCategoryId) {
                $query->where('task_category_id', $taskCategoryId);
            })
            ->orderBy('id')
            ->get();

        $period = CarbonPeriod::create($startDate->copy()->startOfDay(), '1 day', $endDate->copy()->startOfDay());
        $labels = [];
        $planningSeries = [];
        $realizationSeries = [];

        foreach ($period as $day) {
            $dayStart = $day->copy()->startOfDay();
            $dayEnd = $day->copy()->endOfDay();

            $labels[] = $dayStart->format('Y-m-d');
            $planningSeries[] = $tasks->filter(function (TaskMaster $task) use ($dayStart, $dayEnd) {
                return $this->overlapsRange($task->date_planning_start, $task->date_planning_finish, $dayStart, $dayEnd);
            })->count();
            $realizationSeries[] = $tasks->filter(function (TaskMaster $task) use ($dayStart, $dayEnd) {
                return $this->overlapsRange($task->date_realization_start, $task->date_realization_finish, $dayStart, $dayEnd);
            })->count();
        }

        $statusLabels = [
            0 => 'New',
            1 => 'On Process',
            2 => 'Done',
            3 => 'Hold',
        ];

        $statusCounts = collect([0, 1, 2, 3])->mapWithKeys(function (int $status) use ($tasks) {
            return [$status => $tasks->where('status', $status)->count()];
        })->all();

        $taskCategories = TaskCategory::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($taskCategoryId > 0 && ! $taskCategories->pluck('id')->contains($taskCategoryId)) {
            $taskCategoryId = 0;
        }

        $categoryStats = $taskCategories->map(function (TaskCategory $category) use ($tasks) {
            $count = $tasks->filter(function (TaskMaster $task) use ($category) {
                return (int) $task->task_category_id === (int) $category->id;
            })->count();

            return [
                'id' => $category->id,
                'name' => $category->name,
                'total' => $count,
            ];
        })->values();

        $categoryTotal = $categoryStats->sum('total');

        $categoryChartLabels = $categoryStats->pluck('name')->all();
        $categoryChartTotals = $categoryStats->pluck('total')->all();

        $categoryStats = $categoryStats->map(function (array $category) use ($categoryTotal) {
            $category['percentage'] = $categoryTotal > 0 ? round(($category['total'] / $categoryTotal) * 100, 1) : 0;

            return $category;
        });

        $plannerIds = $tasks
            ->map(function (TaskMaster $task) {
                return (int) ($task->planned_by ?? 0);
            })
            ->filter()
            ->unique()
            ->values();

        $plannerNames = User::query()
            ->whereIn('id', $plannerIds)
            ->pluck('name', 'id');

        $summaryStats = $tasks
            ->groupBy(function (TaskMaster $task) {
                return (int) ($task->planned_by ?? 0);
            })
            ->map(function ($group, int $plannedBy) use ($plannerNames) {
                $unfinished = $group->filter(function (TaskMaster $task) {
                    return in_array((int) $task->status, [0, 1, 3], true);
                })->count();

                $finished = $group->where('status', 2)->count();

                return [
                    'planned_by' => $plannedBy,
                    'name' => $plannerNames->get($plannedBy, $plannedBy > 0 ? 'Unknown User' : 'Unknown User'),
                    'total_task' => $group->count(),
                    'unfinished' => $unfinished,
                    'finished' => $finished,
                ];
            })
            ->sortByDesc('total_task')
            ->values();

        $dashboardView = $user?->roles()->where('name', 'administrator')->exists()
            ? 'admin.dashboard'
            : 'manager.dashboard';

        return view($dashboardView, [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'plannedBy' => $plannedBy,
            'taskCategoryId' => $taskCategoryId,
            'adminUsers' => $adminUsers,
            'taskCategories' => $taskCategories,
            'taskDetails' => $tasks,
            'statusLabels' => $statusLabels,
            'statusCounts' => $statusCounts,
            'lineChartLabels' => $labels,
            'planningSeries' => $planningSeries,
            'realizationSeries' => $realizationSeries,
            'categoryStats' => $categoryStats,
            'categoryChartLabels' => $categoryChartLabels,
            'categoryChartTotals' => $categoryChartTotals,
            'summaryStats' => $summaryStats,
            'totalTaskDetails' => $tasks->count(),
        ]);
    }

    private function parseDate(?string $value, Carbon $fallback): Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return $fallback->copy();
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return $fallback->copy();
        }
    }

    private function applyDateOverlap(Builder $query, string $startColumn, string $finishColumn, Carbon $rangeStart, Carbon $rangeEnd): void
    {
        $query->where(function (Builder $builder) use ($startColumn, $finishColumn, $rangeStart, $rangeEnd) {
            $builder->where(function (Builder $nested) use ($startColumn, $finishColumn, $rangeStart, $rangeEnd) {
                $nested->whereNotNull($startColumn)
                    ->whereNotNull($finishColumn)
                    ->whereDate($startColumn, '<=', $rangeEnd->toDateString())
                    ->whereDate($finishColumn, '>=', $rangeStart->toDateString());
            })->orWhere(function (Builder $nested) use ($startColumn, $finishColumn, $rangeStart, $rangeEnd) {
                $nested->whereNotNull($startColumn)
                    ->whereNull($finishColumn)
                    ->whereDate($startColumn, '>=', $rangeStart->toDateString())
                    ->whereDate($startColumn, '<=', $rangeEnd->toDateString());
            })->orWhere(function (Builder $nested) use ($startColumn, $finishColumn, $rangeStart, $rangeEnd) {
                $nested->whereNull($startColumn)
                    ->whereNotNull($finishColumn)
                    ->whereDate($finishColumn, '>=', $rangeStart->toDateString())
                    ->whereDate($finishColumn, '<=', $rangeEnd->toDateString());
            });
        });
    }

    private function overlapsRange(?Carbon $start, ?Carbon $finish, Carbon $rangeStart, Carbon $rangeEnd): bool
    {
        if ($start === null && $finish === null) {
            return false;
        }

        if ($start !== null && $finish !== null) {
            return $start->copy()->startOfDay()->lte($rangeEnd) && $finish->copy()->endOfDay()->gte($rangeStart);
        }

        $singleDate = $start ?? $finish;

        return $singleDate !== null
            && $singleDate->copy()->startOfDay()->lte($rangeEnd)
            && $singleDate->copy()->endOfDay()->gte($rangeStart);
    }
}