<?php

namespace App\Http\Controllers;

use App\Models\TaskCategory;
use App\Models\TaskAttachment;
use App\Models\TaskMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            'detailRows' => old('details', [
                [
                    'activity' => '',
                    'date_planning_start' => '',
                    'date_planning_finish' => '',
                    'description' => '',
                ],
            ]),
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

    public function previewAttachment(TaskAttachment $attachment)
    {
        if (! $attachment->path || ! Storage::disk('public')->exists($attachment->path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($attachment->path));
    }

    public function store(Request $request)
    {
        $data = $this->validateTaskMaster($request);
        $detailPayloads = $this->validateTaskDetails($request);
        $attachmentPayloads = $this->buildAttachmentPayloads($request);

        DB::transaction(function () use ($data, $detailPayloads, $attachmentPayloads) {
            $taskMaster = TaskMaster::create($data);

            if ($detailPayloads !== []) {
                $taskMaster->details()->createMany($detailPayloads);
            }

            if ($attachmentPayloads !== []) {
                $taskMaster->attachments()->createMany($attachmentPayloads);
            }
        });

        return redirect()->route('task-masters.index')
            ->with('success', 'Document created successfully.');
    }

    public function edit(TaskMaster $taskMaster)
    {
        $taskMaster->load('details', 'attachments');

        $detailRows = old('details');

        if ($detailRows === null) {
            $detailRows = $taskMaster->details->map(function ($detail) {
                return [
                    'activity' => $detail->activity,
                    'date_planning_start' => optional($detail->date_planning_start)->format('Y-m-d\TH:i'),
                    'date_planning_finish' => optional($detail->date_planning_finish)->format('Y-m-d\TH:i'),
                    'description' => $detail->description,
                ];
            })->values()->all();
        }

        if ($detailRows === []) {
            $detailRows = [[
                'activity' => '',
                'date_planning_start' => '',
                'date_planning_finish' => '',
                'description' => '',
            ]];
        }

        return view('task-masters.form', [
            'taskMaster' => $taskMaster,
            'categories' => $this->getFormCategories($taskMaster),
            'intervalOptions' => array_keys(self::INTERVAL_OPTIONS),
            'selectedInterval' => array_search((int) $taskMaster->interval_schedule, self::INTERVAL_OPTIONS, true) ?: null,
            'detailRows' => $detailRows,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, TaskMaster $taskMaster)
    {
        $data = $this->validateTaskMaster($request, $taskMaster);
        $detailPayloads = $this->validateTaskDetails($request);
        $attachmentPayloads = $this->buildAttachmentPayloads($request);
        $keptAttachmentIds = $this->validateExistingAttachmentIds($request, $taskMaster);

        DB::transaction(function () use ($taskMaster, $data, $detailPayloads, $attachmentPayloads, $keptAttachmentIds) {
            $taskMaster->update($data);

            $taskMaster->details()->delete();

            if ($detailPayloads !== []) {
                $taskMaster->details()->createMany($detailPayloads);
            }

            $this->deleteRemovedAttachments($taskMaster, $keptAttachmentIds);

            if ($attachmentPayloads !== []) {
                $taskMaster->attachments()->createMany($attachmentPayloads);
            }
        });

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

    private function validateTaskDetails(Request $request): array
    {
        $detailRows = collect($request->input('details', []))
            ->map(function ($detail) {
                return [
                    'activity' => trim((string) ($detail['activity'] ?? '')),
                    'date_planning_start' => $detail['date_planning_start'] ?? null,
                    'date_planning_finish' => $detail['date_planning_finish'] ?? null,
                    'description' => $detail['description'] ?? null,
                ];
            })
            ->filter(function ($detail) {
                return $detail['activity'] !== ''
                    || ! empty($detail['date_planning_start'])
                    || ! empty($detail['date_planning_finish'])
                    || trim((string) ($detail['description'] ?? '')) !== '';
            })
            ->values()
            ->all();

        $request->merge(['details' => $detailRows]);

        $rules = [
            'details' => ['nullable', 'array'],
        ];

        foreach (array_keys($detailRows) as $index) {
            $rules["details.{$index}.activity"] = ['required', 'string', 'max:255'];
            $rules["details.{$index}.date_planning_start"] = ['required', 'date'];
            $rules["details.{$index}.date_planning_finish"] = ['required', 'date', "after_or_equal:details.{$index}.date_planning_start"];
            $rules["details.{$index}.description"] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);

        $details = $validated['details'] ?? [];

        return collect($details)->map(function ($detail) {
            $start = Carbon::parse($detail['date_planning_start']);
            $finish = Carbon::parse($detail['date_planning_finish']);

            return [
                'code' => uniqid(),
                'activity' => $detail['activity'],
                'date_planning_start' => $start,
                'date_planning_finish' => $finish,
                'duration_planning' => $start->diffInHours($finish),
                'description' => $detail['description'] ?? null,
            ];
        })->all();
    }

    private function buildAttachmentPayloads(Request $request): array
    {
        $validated = $request->validate([
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
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

    private function validateExistingAttachmentIds(Request $request, TaskMaster $taskMaster): array
    {
        $validated = $request->validate([
            'existing_attachment_ids' => ['nullable', 'array'],
            'existing_attachment_ids.*' => ['integer'],
        ]);

        $attachmentIds = collect($validated['existing_attachment_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->values();

        $allowedIds = $taskMaster->attachments()->pluck('id');

        if ($attachmentIds->diff($allowedIds)->isNotEmpty()) {
            abort(422, 'Invalid attachment selection.');
        }

        return $attachmentIds->all();
    }

    private function deleteRemovedAttachments(TaskMaster $taskMaster, array $keptAttachmentIds): void
    {
        $attachmentsToDelete = $taskMaster->attachments()
            ->when($keptAttachmentIds !== [], function ($query) use ($keptAttachmentIds) {
                $query->whereNotIn('id', $keptAttachmentIds);
            }, function ($query) {
                $query->whereNotNull('id');
            })
            ->get();

        foreach ($attachmentsToDelete as $attachment) {
            if ($attachment->path) {
                Storage::disk('public')->delete($attachment->path);
            }

            $attachment->delete();
        }
    }
}