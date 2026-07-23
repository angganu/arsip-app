<?php

namespace App\Http\Controllers;

use App\Models\TaskDetail;
use App\Models\TaskMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskDetailController extends Controller
{
    public function create(TaskMaster $taskMaster)
    {
        return view('task-details.form', [
            'taskMaster' => $taskMaster,
        ]);
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
            'duration_planning' => $start->diffInHours($finish),
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
