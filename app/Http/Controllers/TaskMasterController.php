<?php

namespace App\Http\Controllers;

use App\Models\TaskCategory;
use App\Models\TaskAttachment;
use App\Models\TaskMaster;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        $user = $request->user();
        $roleNames = $user?->roles()->pluck('name') ?? collect();
        $isManager = $roleNames->contains('manager');
        $isAdministrator = $roleNames->contains('administrator');

        if (! $isManager && ! $isAdministrator) {
            abort(403, 'You do not have permission to access task data.');
        }

        $perPage = in_array((int) $request->input('per_page', 5), [5, 10, 25, 50, 100], true)
            ? (int) $request->input('per_page', 5)
            : 5;

        $keyword = trim((string) $request->input('keyword', ''));
        $status = $request->input('status');
        $taskCategoryId = (int) $request->input('task_category_id', 0);
        $startDate = $this->parseFilterDate($request->input('start_date'));
        $endDate = $this->parseFilterDate($request->input('end_date'));
        $sortBy = $request->input('sort_by', 'latest');
        $plannedBy = $isManager ? (int) $request->input('planned_by', 0) : 0;
        $adminUsers = collect();
        $taskCategories = TaskCategory::query()->orderBy('name')->get(['id', 'name']);

        if ($taskCategoryId > 0 && ! $taskCategories->pluck('id')->contains($taskCategoryId)) {
            $taskCategoryId = 0;
        }

        if ($startDate !== null && $endDate !== null && $startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [$endDate->copy(), $startDate->copy()];
        }

        if ($isManager) {
            $adminUsers = User::query()
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'administrator');
                })
                ->orderBy('name')
                ->get(['id', 'name']);

            if ($plannedBy > 0 && ! $adminUsers->pluck('id')->contains($plannedBy)) {
                $plannedBy = 0;
            }
        }

        $query = TaskMaster::query()
            ->with('category')
            ->withCount([
                'details',
                'details as done_details_count' => function ($builder) {
                    $builder->where('status', 2);
                },
                'discussions as unread_discussions_count' => function ($builder) use ($user) {
                    $builder->where('is_read', 0)
                        ->where('base_user_id', '!=', $user->id);
                },
            ]);

        if ($isAdministrator && ! $isManager) {
            $query->where('planned_by', $user->id);
        }

        if ($isManager && $plannedBy > 0) {
            $query->where('planned_by', $plannedBy);
        }

        if ($taskCategoryId > 0) {
            $query->where('task_category_id', $taskCategoryId);
        }

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

        if (in_array((string) $status, ['0', '1', '2', '3'], true)) {
            $query->where('status', (int) $status);
        } elseif (in_array($status, ['scheduled', 'unscheduled'], true)) {
            $query->where('has_schedule', $status === 'scheduled');
        }

        if ($startDate !== null && $endDate !== null) {
            $query->where(function ($builder) use ($startDate, $endDate) {
                $builder->where(function ($nested) use ($startDate, $endDate) {
                    $nested->whereNotNull('date_planning_start')
                        ->whereNotNull('date_planning_finish')
                        ->whereDate('date_planning_start', '<=', $endDate->toDateString())
                        ->whereDate('date_planning_finish', '>=', $startDate->toDateString());
                })->orWhere(function ($nested) use ($startDate, $endDate) {
                    $nested->whereNotNull('date_realization_start')
                        ->whereNotNull('date_realization_finish')
                        ->whereDate('date_realization_start', '<=', $endDate->toDateString())
                        ->whereDate('date_realization_finish', '>=', $startDate->toDateString());
                });
            });
        }

        if ($sortBy === 'oldest') {
            $query->orderBy('created_at', 'asc')->orderBy('name', 'asc');
        } else {
            $query->orderBy('created_at', 'desc')->orderBy('name', 'asc');
        }

        $tasks = $query->paginate($perPage)
            ->appends($request->only(['per_page', 'keyword', 'status', 'sort_by', 'planned_by', 'task_category_id', 'start_date', 'end_date']));

        $startDateInput = $startDate?->format('Y-m-d') ?? (string) $request->input('start_date', '');
        $endDateInput = $endDate?->format('Y-m-d') ?? (string) $request->input('end_date', '');

        return view('task-masters.index', compact('tasks', 'perPage', 'keyword', 'status', 'sortBy', 'plannedBy', 'taskCategoryId', 'taskCategories', 'adminUsers', 'isManager', 'startDateInput', 'endDateInput'));
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
        $taskMaster->load([
            'category',
            'details' => function ($query) {
                $query->orderBy('date_planning_start')->orderBy('id');
            },
            'attachments' => function ($query) {
                $query->orderBy('id');
            },
        ]);

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
            ->with('success', __('texts.success_document_created'));
    }

    public function edit(TaskMaster $taskMaster)
    {
        $taskMaster->load('details', 'attachments');

        $detailRows = old('details');

        if ($detailRows === null) {
            $detailRows = $taskMaster->details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'status' => (int) $detail->status,
                    'activity' => $detail->activity,
                    'date_planning_start' => optional($detail->date_planning_start)->format('Y-m-d\TH:i'),
                    'date_planning_finish' => optional($detail->date_planning_finish)->format('Y-m-d\TH:i'),
                    'description' => $detail->description,
                ];
            })->values()->all();
        }

        if ($detailRows === []) {
            $detailRows = [[
                'id' => null,
                'status' => 0,
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
        $detailPayloads = $this->validateTaskDetails($request, $taskMaster);
        $attachmentPayloads = $this->buildAttachmentPayloads($request);
        $keptAttachmentIds = $this->validateExistingAttachmentIds($request, $taskMaster);

        DB::transaction(function () use ($taskMaster, $data, $detailPayloads, $attachmentPayloads, $keptAttachmentIds) {
            $taskMaster->update($data);

            $existingDetails = $taskMaster->details()->get()->keyBy('id');
            $submittedIds = collect($detailPayloads)
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->values();

            $lockedIds = $existingDetails
                ->filter(fn ($detail) => (int) $detail->status === 2)
                ->keys()
                ->map(fn ($id) => (int) $id)
                ->values();

            if ($lockedIds->diff($submittedIds)->isNotEmpty()) {
                abort(422, 'Completed task details cannot be removed.');
            }

            $editableExistingIds = $existingDetails
                ->filter(fn ($detail) => (int) $detail->status !== 2)
                ->keys()
                ->map(fn ($id) => (int) $id);

            $idsToDelete = $editableExistingIds->diff($submittedIds);

            if ($idsToDelete->isNotEmpty()) {
                $taskMaster->details()->whereIn('id', $idsToDelete->all())->delete();
            }

            foreach ($detailPayloads as $detailPayload) {
                $detailId = (int) ($detailPayload['id'] ?? 0);

                if ($detailId > 0) {
                    $existingDetail = $existingDetails->get($detailId);

                    if (! $existingDetail) {
                        abort(422, 'Invalid task detail selection.');
                    }

                    if ((int) $existingDetail->status === 2) {
                        continue;
                    }

                    $existingDetail->update(Arr::except($detailPayload, ['id', 'status', 'code']));
                    continue;
                }

                $taskMaster->details()->create(Arr::except($detailPayload, ['id', 'status']));
            }

            $this->deleteRemovedAttachments($taskMaster, $keptAttachmentIds);

            if ($attachmentPayloads !== []) {
                $taskMaster->attachments()->createMany($attachmentPayloads);
            }
        });

        return redirect()->route('task-masters.index')
            ->with('success', __('texts.success_document_updated'));
    }

    public function destroy(TaskMaster $taskMaster)
    {
        $taskMaster->delete();

        return redirect()->route('task-masters.index')
            ->with('success', __('texts.success_document_deleted'));
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

    private function validateTaskDetails(Request $request, ?TaskMaster $taskMaster = null): array
    {
        $detailRows = collect($request->input('details', []))
            ->map(function ($detail) {
                return [
                    'id' => isset($detail['id']) ? (int) $detail['id'] : null,
                    'status' => isset($detail['status']) ? (int) $detail['status'] : 0,
                    'activity' => trim((string) ($detail['activity'] ?? '')),
                    'date_planning_start' => $detail['date_planning_start'] ?? null,
                    'date_planning_finish' => $detail['date_planning_finish'] ?? null,
                    'description' => $detail['description'] ?? null,
                ];
            })
            ->filter(function ($detail) {
                return ($detail['id'] ?? 0) > 0
                    || $detail['activity'] !== ''
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
            $isLockedRow = ((int) ($detailRows[$index]['status'] ?? 0)) === 2;

            $rules["details.{$index}.id"] = ['nullable', 'integer'];
            $rules["details.{$index}.status"] = ['nullable', 'integer'];

            if ($isLockedRow) {
                $rules["details.{$index}.activity"] = ['nullable', 'string', 'max:255'];
                $rules["details.{$index}.date_planning_start"] = ['nullable', 'date'];
                $rules["details.{$index}.date_planning_finish"] = ['nullable', 'date', "after_or_equal:details.{$index}.date_planning_start"];
                $rules["details.{$index}.description"] = ['nullable', 'string'];
                continue;
            }

            $rules["details.{$index}.activity"] = ['required', 'string', 'max:255'];
            $rules["details.{$index}.date_planning_start"] = ['required', 'date'];
            $rules["details.{$index}.date_planning_finish"] = ['required', 'date', "after_or_equal:details.{$index}.date_planning_start"];
            $rules["details.{$index}.description"] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);

        $details = $validated['details'] ?? [];

        if ($taskMaster) {
            $allowedDetailIds = $taskMaster->details()->pluck('id')->map(fn ($id) => (int) $id);

            $submittedDetailIds = collect($details)
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id);

            if ($submittedDetailIds->diff($allowedDetailIds)->isNotEmpty()) {
                abort(422, 'Invalid task detail selection.');
            }
        }

        return collect($details)->map(function ($detail) {
            $detailId = isset($detail['id']) ? (int) $detail['id'] : 0;
            $detailStatus = isset($detail['status']) ? (int) $detail['status'] : 0;

            if ($detailId > 0 && $detailStatus === 2) {
                return [
                    'id' => $detailId,
                    'status' => $detailStatus,
                ];
            }

            $start = Carbon::parse($detail['date_planning_start']);
            $finish = Carbon::parse($detail['date_planning_finish']);

            $payload = [
                'id' => $detailId > 0 ? $detailId : null,
                'status' => $detailStatus,
                'activity' => $detail['activity'],
                'date_planning_start' => $start,
                'date_planning_finish' => $finish,
                'duration_planning' => $start->diffInHours($finish),
                'description' => $detail['description'] ?? null,
            ];

            if ($detailId === 0) {
                $payload['code'] = uniqid();
            }

            return $payload;
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