<?php

namespace App\Http\Controllers;

use App\Models\TaskCategory;
use App\Models\TaskMaster;
use App\Models\TaskMasterArchive;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskMasterArchiveController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isManager = $this->isManager($user);
        $isAdministrator = $this->isAdministrator($user);

        if (! $isManager && ! $isAdministrator) {
            abort(403, 'You do not have permission to access archive data.');
        }

        $keyword = trim((string) $request->input('keyword', ''));
        $planningStartDate = $this->parseFilterDate($request->input('planning_start_date'));
        $planningEndDate = $this->parseFilterDate($request->input('planning_end_date'));
        $realizationStartDate = $this->parseFilterDate($request->input('realization_start_date'));
        $realizationEndDate = $this->parseFilterDate($request->input('realization_end_date'));
        $taskCategoryId = (int) $request->input('task_category_id', 0);
        $plannedBy = $isManager ? (int) $request->input('planned_by', 0) : (int) ($user?->id ?? 0);

        if ($planningStartDate !== null && $planningEndDate !== null && $planningStartDate->greaterThan($planningEndDate)) {
            [$planningStartDate, $planningEndDate] = [$planningEndDate->copy(), $planningStartDate->copy()];
        }

        if ($realizationStartDate !== null && $realizationEndDate !== null && $realizationStartDate->greaterThan($realizationEndDate)) {
            [$realizationStartDate, $realizationEndDate] = [$realizationEndDate->copy(), $realizationStartDate->copy()];
        }

        $taskCategories = TaskCategory::query()->orderBy('name')->get(['id', 'name']);

        if ($taskCategoryId > 0 && ! $taskCategories->pluck('id')->contains($taskCategoryId)) {
            $taskCategoryId = 0;
        }

        $adminUsers = $isManager
            ? User::query()
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'administrator');
                })
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect([(object) [
                'id' => (int) ($user?->id ?? 0),
                'name' => (string) ($user?->name ?? ''),
            ]]);

        if ($isManager && $plannedBy > 0 && ! $adminUsers->pluck('id')->contains($plannedBy)) {
            $plannedBy = 0;
        }

        $archives = TaskMasterArchive::query()
            ->with(['taskMaster:id,code,name,planned_by,task_category_id,date_planning_start,date_planning_finish,date_realization_start,date_realization_finish', 'taskMaster.planner:id,name', 'generator:id,name'])
            ->when(! $isManager && $isAdministrator, function ($query) use ($user) {
                $query->whereHas('taskMaster', function ($taskQuery) use ($user) {
                    $taskQuery->where('planned_by', (int) $user->id);
                });
            })
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->whereHas('taskMaster', function ($taskQuery) use ($keyword) {
                    $taskQuery->where(function ($nested) use ($keyword) {
                        $nested->where('code', 'like', "%{$keyword}%")
                            ->orWhere('name', 'like', "%{$keyword}%");
                    });
                });
            })
            ->when($taskCategoryId > 0, function ($query) use ($taskCategoryId) {
                $query->whereHas('taskMaster', function ($taskQuery) use ($taskCategoryId) {
                    $taskQuery->where('task_category_id', $taskCategoryId);
                });
            })
            ->when($plannedBy > 0, function ($query) use ($plannedBy) {
                $query->whereHas('taskMaster', function ($taskQuery) use ($plannedBy) {
                    $taskQuery->where('planned_by', $plannedBy);
                });
            })
            ->when($planningStartDate !== null && $planningEndDate !== null, function ($query) use ($planningStartDate, $planningEndDate) {
                $query->whereHas('taskMaster', function ($taskQuery) use ($planningStartDate, $planningEndDate) {
                    $taskQuery->whereNotNull('date_planning_start')
                        ->whereNotNull('date_planning_finish')
                        ->whereDate('date_planning_start', '<=', $planningEndDate->toDateString())
                        ->whereDate('date_planning_finish', '>=', $planningStartDate->toDateString());
                });
            })
            ->when($realizationStartDate !== null && $realizationEndDate !== null, function ($query) use ($realizationStartDate, $realizationEndDate) {
                $query->whereHas('taskMaster', function ($taskQuery) use ($realizationStartDate, $realizationEndDate) {
                    $taskQuery->whereNotNull('date_realization_start')
                        ->whereNotNull('date_realization_finish')
                        ->whereDate('date_realization_start', '<=', $realizationEndDate->toDateString())
                        ->whereDate('date_realization_finish', '>=', $realizationStartDate->toDateString());
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->appends($request->only([
                'keyword',
                'planning_start_date',
                'planning_end_date',
                'realization_start_date',
                'realization_end_date',
                'task_category_id',
                'planned_by',
            ]));

        $planningStartDateInput = $planningStartDate?->format('Y-m-d') ?? (string) $request->input('planning_start_date', '');
        $planningEndDateInput = $planningEndDate?->format('Y-m-d') ?? (string) $request->input('planning_end_date', '');
        $realizationStartDateInput = $realizationStartDate?->format('Y-m-d') ?? (string) $request->input('realization_start_date', '');
        $realizationEndDateInput = $realizationEndDate?->format('Y-m-d') ?? (string) $request->input('realization_end_date', '');

        return view('task-master-archives.index', [
            'archives' => $archives,
            'dashboardRoute' => $isManager ? route('manager.dashboard') : route('admin.dashboard'),
            'keyword' => $keyword,
            'planningStartDateInput' => $planningStartDateInput,
            'planningEndDateInput' => $planningEndDateInput,
            'realizationStartDateInput' => $realizationStartDateInput,
            'realizationEndDateInput' => $realizationEndDateInput,
            'taskCategoryId' => $taskCategoryId,
            'taskCategories' => $taskCategories,
            'plannedBy' => $plannedBy,
            'adminUsers' => $adminUsers,
            'isManager' => $isManager,
        ]);
    }

    private function parseFilterDate(mixed $value): ?Carbon
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

    public function store(Request $request, TaskMaster $taskMaster)
    {
        $user = $request->user();
        $isManager = $this->isManager($user);
        $isAdministrator = $this->isAdministrator($user);

        if (! $isManager && ! $isAdministrator) {
            abort(403, 'You do not have permission to archive task data.');
        }

        if (! $isManager && $isAdministrator && (int) $taskMaster->planned_by !== (int) $user?->id) {
            abort(403, 'You can only archive your own task data.');
        }

        $taskMaster->load([
            'category',
            'planner:id,name',
            'details' => function ($query) {
                $query->with('attachments')->orderBy('date_planning_start')->orderBy('id');
            },
            'attachments' => function ($query) {
                $query->orderBy('id');
            },
        ]);

        $intervalLabel = $this->getIntervalLabel((int) $taskMaster->interval_schedule);

        $pdf = Pdf::loadView('task-masters.archive-pdf', [
            'taskMaster' => $taskMaster,
            'intervalLabel' => $intervalLabel,
            'generatedAt' => now(),
        ])->setPaper('a4');

        $filename = sprintf(
            'task-master-%s-%s.pdf',
            Str::slug($taskMaster->code ?: (string) $taskMaster->id),
            now()->format('Ymd_His')
        );

        $relativePath = 'task-archives/' . $filename;
        $pdfBinary = $pdf->output();

        Storage::disk('public')->put($relativePath, $pdfBinary);

        TaskMasterArchive::create([
            'task_master_id' => $taskMaster->id,
            'name' => $filename,
            'path' => $relativePath,
            'extension' => 'pdf',
            'size' => (int) ceil(strlen($pdfBinary) / 1024),
            'description' => __('texts.archive_generated_for_task', ['name' => $taskMaster->name]),
            'generated_by' => $user?->id,
            'is_active' => true,
            'created_by' => $user?->id,
        ]);

        $taskMaster->update([
            // 'status' => 2,
            'archived' => 1,
        ]);

        return response()->streamDownload(function () use ($pdfBinary) {
            echo $pdfBinary;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function download(Request $request, TaskMasterArchive $taskMasterArchive)
    {
        $user = $request->user();
        $isManager = $this->isManager($user);
        $isAdministrator = $this->isAdministrator($user);

        if (! $isManager && ! $isAdministrator) {
            abort(403, 'You do not have permission to download archive data.');
        }

        $taskMasterArchive->loadMissing('taskMaster:id,planned_by');

        if (! $isManager && $isAdministrator && (int) $taskMasterArchive->taskMaster?->planned_by !== (int) $user?->id) {
            abort(403, 'You can only download your own task archives.');
        }

        if (! $taskMasterArchive->path || ! Storage::disk('public')->exists($taskMasterArchive->path)) {
            abort(404);
        }

        return response()->download(
            Storage::disk('public')->path($taskMasterArchive->path),
            $taskMasterArchive->name ?: basename((string) $taskMasterArchive->path),
            ['Content-Type' => 'application/pdf']
        );
    }

    private function getIntervalLabel(int $intervalSchedule): string
    {
        return match ($intervalSchedule) {
            1 => __('texts.day'),
            2 => __('texts.week'),
            3 => __('texts.month'),
            4 => __('texts.year'),
            default => __('texts.no_schedule'),
        };
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
