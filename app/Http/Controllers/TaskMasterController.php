<?php

namespace App\Http\Controllers;

use App\Models\TaskCategory;
use App\Models\TaskMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskMasterController extends Controller
{
    private const INTERVAL_OPTIONS = [
        'day' => 1,
        'week' => 2,
        'month' => 3,
        'year' => 4,
    ];

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page', 5), [5, 10, 25, 50, 100], true)
            ? (int) $request->input('per_page', 5)
            : 5;

        $keyword = trim((string) $request->input('keyword', ''));
        $status = $request->input('status');
        $sortBy = $request->input('sort_by', 'latest');

        $query = TaskMaster::query()->with('category');

        if ($keyword !== '') {
            $query->where(function ($builder) use ($keyword) {
                $builder->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($keyword) {
                        $categoryQuery->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        if (in_array($status, ['scheduled', 'unscheduled'], true)) {
            $query->where('has_schedule', $status === 'scheduled');
        }

        if ($sortBy === 'oldest') {
            $query->orderBy('created_at', 'asc')->orderBy('name', 'asc');
        } else {
            $query->orderBy('created_at', 'desc')->orderBy('name', 'asc');
        }

        $tasks = $query->paginate($perPage)
            ->appends($request->only(['per_page', 'keyword', 'status', 'sort_by']));

        return view('task-masters.index', compact('tasks', 'perPage', 'keyword', 'status', 'sortBy'));
    }

    public function create()
    {
        return view('task-masters.form', [
            'taskMaster' => new TaskMaster(),
            'categories' => $this->getFormCategories(),
            'intervalOptions' => array_keys(self::INTERVAL_OPTIONS),
            'selectedInterval' => null,
            'mode' => 'create',
        ]);
    }

    public function show(TaskMaster $taskMaster)
    {
        $taskMaster->load('category');

        return view('task-masters.detail', [
            'taskMaster' => $taskMaster,
            'intervalLabel' => $this->getIntervalLabel((int) $taskMaster->interval_schedule),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateTaskMaster($request);

        TaskMaster::create($data);

        return redirect()->route('task-masters.index')
            ->with('success', 'Document created successfully.');
    }

    public function edit(TaskMaster $taskMaster)
    {
        return view('task-masters.form', [
            'taskMaster' => $taskMaster,
            'categories' => $this->getFormCategories($taskMaster),
            'intervalOptions' => array_keys(self::INTERVAL_OPTIONS),
            'selectedInterval' => array_search((int) $taskMaster->interval_schedule, self::INTERVAL_OPTIONS, true) ?: null,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, TaskMaster $taskMaster)
    {
        $data = $this->validateTaskMaster($request, $taskMaster);

        $taskMaster->update($data);

        return redirect()->route('task-masters.index')
            ->with('success', 'Document updated successfully.');
    }

    public function destroy(TaskMaster $taskMaster)
    {
        $taskMaster->delete();

        return redirect()->route('task-masters.index')
            ->with('success', 'Document deleted successfully.');
    }

    private function validateTaskMaster(Request $request, ?TaskMaster $taskMaster = null): array
    {
        $data = $request->validate([
            'task_category_id' => ['required', 'exists:task_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'date_planning_start' => ['required', 'date'],
            'date_planning_finish' => ['required', 'date', 'after_or_equal:date_planning_start'],
            'has_schedule' => ['nullable', 'boolean'],
            'interval_schedule' => ['nullable', 'required_if:has_schedule,1', 'in:' . implode(',', array_keys(self::INTERVAL_OPTIONS))],
            'interval_value' => ['nullable', 'required_if:has_schedule,1', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ]);

        $hasSchedule = $request->boolean('has_schedule');
        $startDate = Carbon::parse($data['date_planning_start']);
        $finishDate = Carbon::parse($data['date_planning_finish']);

        $data['code'] = $taskMaster?->code ?: uniqid();
        $data['planned_by'] = $taskMaster?->planned_by ?: Auth::id();
        $data['has_schedule'] = $hasSchedule;
        $data['interval_schedule'] = $hasSchedule
            ? self::INTERVAL_OPTIONS[$data['interval_schedule'] ?? 'day']
            : 0;
        $data['interval_value'] = $hasSchedule
            ? (int) ($data['interval_value'] ?? 1)
            : 0;
        $data['duration_planning'] = $startDate->diffInDays($finishDate);

        if (! $hasSchedule) {
            $data['interval_schedule'] = 0;
            $data['interval_value'] = 0;
        }

        return $data;
    }

    private function getFormCategories(?TaskMaster $taskMaster = null)
    {
        return TaskCategory::query()
            ->where(function ($query) use ($taskMaster) {
                $query->where('is_active', true);

                if ($taskMaster?->task_category_id) {
                    $query->orWhere('id', $taskMaster->task_category_id);
                }
            })
            ->orderBy('name')
            ->get();
    }

    private function getIntervalLabel(int $intervalSchedule): string
    {
        return match ($intervalSchedule) {
            1 => 'Days',
            2 => 'Weeks',
            3 => 'Months',
            4 => 'Years',
            default => 'No schedule',
        };
    }
}