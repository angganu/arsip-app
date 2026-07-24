<?php

namespace App\Http\Controllers;

use App\Exports\TaskMasterReportExport;
use App\Models\TaskCategory;
use App\Models\TaskMaster;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TaskMasterReportController extends Controller
{
    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function index(Request $request)
    {
        $filters = $this->normalizeFilters($request);
        $user = $request->user();

        $query = $this->buildBaseQuery($request, $filters, $user);

        $tasks = (clone $query)
            ->paginate($filters['perPage'])
            ->appends($request->query());

        $metricRows = (clone $query)->get(['id', 'date_realization_start', 'date_realization_finish']);

        $totalTasks = $metricRows->count();
        $totalDetails = (int) $metricRows->sum(function (TaskMaster $task) {
            return (int) ($task->details_count ?? 0);
        });
        $doneDetails = (int) $metricRows->sum(function (TaskMaster $task) {
            return (int) ($task->done_details_count ?? 0);
        });
        $realizedTasks = (int) $metricRows->filter(function (TaskMaster $task) {
            return $task->date_realization_start !== null || $task->date_realization_finish !== null;
        })->count();

        $completionRate = $totalDetails > 0
            ? round(($doneDetails / $totalDetails) * 100, 1)
            : 0.0;

        $realizationRate = $totalTasks > 0
            ? round(($realizedTasks / $totalTasks) * 100, 1)
            : 0.0;

        return view('reports.task-masters.index', [
            'tasks' => $tasks,
            'filters' => $filters,
            'taskCategories' => TaskCategory::query()->orderBy('name')->get(['id', 'name']),
            'adminUsers' => $this->getAdminUsers(),
            'isManager' => $this->isManager($user),
            'isAdministrator' => $this->isAdministrator($user),
            'metrics' => [
                'total_tasks' => $totalTasks,
                'total_details' => $totalDetails,
                'done_details' => $doneDetails,
                'realized_tasks' => $realizedTasks,
                'completion_rate' => $completionRate,
                'realization_rate' => $realizationRate,
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->normalizeFilters($request);
        $user = $request->user();

        $tasks = $this->buildBaseQuery($request, $filters, $user)
            ->orderBy('created_at', 'desc')
            ->limit(10000)
            ->get();

        $dateStamp = now()->format('Ymd_His');
        $prefix = __('texts.report_export_filename_prefix');
        $filename = sprintf('%s_%s.xlsx', str_replace(' ', '-', strtolower((string) $prefix)), $dateStamp);

        return Excel::download(new TaskMasterReportExport($tasks, $filters), $filename);
    }

    private function buildBaseQuery(Request $request, array $filters, ?User $user)
    {
        $isManager = $this->isManager($user);
        $isAdministrator = $this->isAdministrator($user);

        if (! $isManager && ! $isAdministrator) {
            abort(403, 'You do not have permission to access report data.');
        }

        $query = TaskMaster::query()
            ->with(['category:id,name', 'planner:id,name'])
            ->withCount([
                'details',
                'details as done_details_count' => function (Builder $builder) {
                    $builder->where('status', 2);
                },
                'details as on_progress_details_count' => function (Builder $builder) {
                    $builder->where('status', 1);
                },
                'details as new_details_count' => function (Builder $builder) {
                    $builder->where('status', 0);
                },
                'details as hold_details_count' => function (Builder $builder) {
                    $builder->where('status', 3);
                },
            ]);

        if ($isAdministrator && ! $isManager) {
            $query->where('planned_by', (int) $user?->id);
        }

        if ($isManager && $filters['planned_by'] > 0) {
            $query->where('planned_by', $filters['planned_by']);
        }

        if ($filters['task_category_id'] > 0) {
            $query->where('task_category_id', $filters['task_category_id']);
        }

        if ($filters['keyword'] !== '') {
            $keyword = $filters['keyword'];
            $query->where(function (Builder $builder) use ($keyword) {
                $builder->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhereHas('category', function (Builder $categoryQuery) use ($keyword) {
                        $categoryQuery->where('name', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('planner', function (Builder $plannerQuery) use ($keyword) {
                        $plannerQuery->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        if (in_array((string) $filters['status'], ['0', '1', '2', '3'], true)) {
            $query->where('status', (int) $filters['status']);
        }

        if ($filters['has_schedule'] === '1') {
            $query->where('has_schedule', true);
        } elseif ($filters['has_schedule'] === '0') {
            $query->where('has_schedule', false);
        }

        if ($filters['start_date'] !== null && $filters['end_date'] !== null) {
            $startDate = $filters['start_date'];
            $endDate = $filters['end_date'];

            $query->where(function (Builder $builder) use ($startDate, $endDate) {
                $builder->where(function (Builder $nested) use ($startDate, $endDate) {
                    $nested->whereNotNull('date_planning_start')
                        ->whereNotNull('date_planning_finish')
                        ->whereDate('date_planning_start', '<=', $endDate->toDateString())
                        ->whereDate('date_planning_finish', '>=', $startDate->toDateString());
                })->orWhere(function (Builder $nested) use ($startDate, $endDate) {
                    $nested->whereNotNull('date_realization_start')
                        ->whereNotNull('date_realization_finish')
                        ->whereDate('date_realization_start', '<=', $endDate->toDateString())
                        ->whereDate('date_realization_finish', '>=', $startDate->toDateString());
                })->orWhere(function (Builder $nested) use ($startDate, $endDate) {
                    $nested->whereNotNull('date_planning_start')
                        ->whereNull('date_planning_finish')
                        ->whereDate('date_planning_start', '>=', $startDate->toDateString())
                        ->whereDate('date_planning_start', '<=', $endDate->toDateString());
                });
            });
        }

        if ($filters['sort_by'] === 'oldest') {
            $query->orderBy('created_at', 'asc')->orderBy('name', 'asc');
        } else {
            $query->orderBy('created_at', 'desc')->orderBy('name', 'asc');
        }

        return $query;
    }

    private function normalizeFilters(Request $request): array
    {
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 10;
        }

        $startDate = $this->parseDate($request->input('start_date'));
        $endDate = $this->parseDate($request->input('end_date'));

        if ($startDate !== null && $endDate !== null && $startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [$endDate->copy(), $startDate->copy()];
        }

        return [
            'perPage' => $perPage,
            'keyword' => trim((string) $request->input('keyword', '')),
            'status' => (string) $request->input('status', ''),
            'sort_by' => in_array((string) $request->input('sort_by', 'latest'), ['latest', 'oldest'], true)
                ? (string) $request->input('sort_by', 'latest')
                : 'latest',
            'planned_by' => (int) $request->input('planned_by', 0),
            'task_category_id' => (int) $request->input('task_category_id', 0),
            'has_schedule' => in_array((string) $request->input('has_schedule', ''), ['0', '1'], true)
                ? (string) $request->input('has_schedule')
                : '',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_date_input' => $startDate?->format('Y-m-d') ?? (string) $request->input('start_date', ''),
            'end_date_input' => $endDate?->format('Y-m-d') ?? (string) $request->input('end_date', ''),
        ];
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function getAdminUsers()
    {
        return User::query()
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', 'administrator');
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function isManager(?User $user): bool
    {
        return (bool) $user?->roles()->where('name', 'manager')->exists();
    }

    private function isAdministrator(?User $user): bool
    {
        return (bool) $user?->roles()->where('name', 'administrator')->exists();
    }
}
