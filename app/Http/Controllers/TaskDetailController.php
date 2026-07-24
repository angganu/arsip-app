<?php

namespace App\Http\Controllers;

use App\Models\TaskAttachment;
use App\Models\TaskDetail;
use App\Models\TaskMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskDetailController extends Controller
{
    public function create(TaskMaster $taskMaster)
    {
        return view('task-details.form', [
            'taskMaster' => $taskMaster,
        ]);
    }

    public function editRealization(TaskMaster $taskMaster, TaskDetail $taskDetail)
    {
        return view('task-details.realization-form', [
            'taskMaster' => $taskMaster,
            'taskDetail' => $taskDetail,
        ]);
    }

    public function submitRealization(Request $request, TaskMaster $taskMaster, TaskDetail $taskDetail)
    {
        $data = $request->validate([
            'date_realization_start' => ['required', 'date'],
            'date_realization_finish' => ['required', 'date', 'after_or_equal:date_realization_start'],
            'note' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $start = Carbon::parse($data['date_realization_start']);
        $finish = Carbon::parse($data['date_realization_finish']);

        $taskDetail->update([
            'date_realization_start' => $start,
            'date_realization_finish' => $finish,
            'duration_realization' => $start->diffInDays($finish),
            'note' => $data['note'] ?? null,
            'status' => 2,
            'updated_by' => Auth::id(),
        ]);

        $this->syncTaskMasterStatus($taskMaster);

        foreach ($this->buildAttachmentPayloads($request) as $attachmentPayload) {
            $taskDetail->attachments()->create([
                'task_master_id' => $taskMaster->id,
                ...$attachmentPayload,
            ]);
        }

        return redirect()->route('task-masters.show', $taskMaster)
            ->with('success', __('texts.success_detail_realization_updated'));
    }

    private function syncTaskMasterStatus(TaskMaster $taskMaster): void
    {
        $details = $taskMaster->details()->get();

        if ($details->isEmpty()) {
            return;
        }

        $allCompleted = $details->every(fn (TaskDetail $detail) => (int) $detail->status === 2);

        $taskMaster->update([
            'status' => $allCompleted ? 2 : 1,
        ]);
    }

    private function buildAttachmentPayloads(Request $request): array
    {
        $validated = $request->validate([
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $attachments = $validated['attachments'] ?? [];

        return collect($attachments)->map(function ($file) {
            $storedPath = $file->store('task-attachments', 'public');

            return [
                'name' => pathinfo($storedPath, PATHINFO_BASENAME),
                'original_name' => $file->getClientOriginalName(),
                'path' => $storedPath,
                'extension' => $file->getClientOriginalExtension(),
                'size' => (int) ceil($file->getSize() / 1024),
                'created_by' => Auth::id(),
            ];
        })->all();
    }

    public function store(Request $request, TaskMaster $taskMaster)
    {
        $data = $request->validate([
            'activity' => ['required', 'string', 'max:255'],
            'date_planning_start' => ['required', 'date'],
            'date_planning_finish' => ['required', 'date', 'after_or_equal:date_planning_start'],
            'description' => ['nullable', 'string'],
        ]);

        $start = Carbon::parse($data['date_planning_start']);
        $finish = Carbon::parse($data['date_planning_finish']);

        TaskDetail::create([
            'code' => uniqid(),
            'task_master_id' => $taskMaster->id,
            'activity' => $data['activity'],
            'date_planning_start' => $start,
            'date_planning_finish' => $finish,
            'duration_planning' => $start->diffInDays($finish),
            'description' => $data['description'] ?? null,
            'status' => 0,
            'is_active' => true,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('task-masters.show', $taskMaster)
            ->with('success', __('texts.success_detail_created'));
    }
}
