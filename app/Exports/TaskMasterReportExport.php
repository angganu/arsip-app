<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class TaskMasterReportExport implements FromArray, ShouldAutoSize, WithTitle
{
    public function __construct(
        private readonly Collection $tasks,
        private readonly array $filters,
    ) {
    }

    public function array(): array
    {
        $rows = [
            [
                'No',
                'Code',
                'Task Name',
                'Category',
                'Planner',
                'Status',
                'Scheduled',
                'Planning Start',
                'Planning Finish',
                'Realization Start',
                'Realization Finish',
                'Details Total',
                'Details Done',
                'Details Progress (%)',
                'Description',
            ],
        ];

        foreach ($this->tasks->values() as $index => $task) {
            $detailsCount = (int) ($task->details_count ?? 0);
            $doneDetailsCount = (int) ($task->done_details_count ?? 0);
            $progress = $detailsCount > 0
                ? round(($doneDetailsCount / $detailsCount) * 100, 1)
                : 0;

            $rows[] = [
                $index + 1,
                (string) ($task->code ?? ''),
                (string) ($task->name ?? ''),
                (string) ($task->category?->name ?? __('texts.uncategorized')),
                (string) ($task->planner?->name ?? __('texts.unknown_user')),
                $this->mapStatus((int) ($task->status ?? 0)),
                (bool) $task->has_schedule ? __('texts.scheduled') : __('texts.no_schedule'),
                optional($task->date_planning_start)?->format('Y-m-d') ?? '',
                optional($task->date_planning_finish)?->format('Y-m-d') ?? '',
                optional($task->date_realization_start)?->format('Y-m-d') ?? '',
                optional($task->date_realization_finish)?->format('Y-m-d') ?? '',
                $detailsCount,
                $doneDetailsCount,
                $progress,
                (string) ($task->description ?? ''),
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return __('texts.task_master_report');
    }

    private function mapStatus(int $status): string
    {
        return match ($status) {
            1 => __('texts.on_progress'),
            2 => __('texts.done'),
            3 => __('texts.hold'),
            default => __('texts.new_task'),
        };
    }
}
