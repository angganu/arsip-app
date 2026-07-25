<?php

namespace App\Http\Controllers;

use App\Models\TaskMaster;
use App\Models\TaskMasterArchive;
use App\Models\User;
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

        $archives = TaskMasterArchive::query()
            ->with(['taskMaster:id,code,name,planned_by', 'taskMaster.planner:id,name', 'generator:id,name'])
            ->when(! $isManager && $isAdministrator, function ($query) use ($user) {
                $query->whereHas('taskMaster', function ($taskQuery) use ($user) {
                    $taskQuery->where('planned_by', (int) $user->id);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('task-master-archives.index', [
            'archives' => $archives,
            'dashboardRoute' => $isManager ? route('manager.dashboard') : route('admin.dashboard'),
        ]);
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

        $pdf = app('dompdf.wrapper')->loadView('task-masters.archive-pdf', [
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
