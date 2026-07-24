@extends('layouts.app')

@section('title', __('texts.task_master_report'))

@push('styles')
    <style>
        .report-filter-card {
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(15, 23, 42, 0.55);
            border-radius: 0.9rem;
            padding: 0.95rem;
            margin-bottom: 1rem;
        }

        .report-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .report-metric {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.85rem;
            background: rgba(15, 23, 42, 0.72);
            padding: 0.8rem;
        }

        .report-metric__label {
            color: #cbd5e1;
            font-size: 0.8rem;
            margin-bottom: 0.2rem;
        }

        .report-metric__value {
            color: #f8fafc;
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .report-table-card {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.9rem;
            background: rgba(15, 23, 42, 0.55);
            padding: 0.7rem;
        }

        .report-table {
            --bs-table-bg: transparent;
            --bs-table-color: #e2e8f0;
            --bs-table-border-color: rgba(255, 255, 255, 0.12);
            margin-bottom: 0;
            min-width: 860px;
        }

        .report-progress {
            min-width: 84px;
            text-align: right;
            font-variant-numeric: tabular-nums;
            font-weight: 700;
            color: #f8fafc;
        }

        .badge-status {
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .pagination-dark .page-item .page-link {
            background-color: #1f2937;
            border-color: #374151;
            color: #f8fafc;
        }

        .pagination-dark .page-item .page-link:hover,
        .pagination-dark .page-item .page-link:focus {
            background-color: #374151;
            border-color: #4b5563;
            color: #ffffff;
        }

        .pagination-dark .page-item.active .page-link {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }

        .pagination-dark .page-item.disabled .page-link {
            background-color: #111827;
            border-color: #374151;
            color: #6b7280;
        }

        @media (min-width: 768px) {
            .report-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    </style>
@endpush

@section('content')
    @php
        $dashboardRoute = ($isManager ?? false) ? route('manager.dashboard') : route('admin.dashboard');
        $queryForExport = [
            'keyword' => $filters['keyword'] ?? '',
            'status' => $filters['status'] ?? '',
            'sort_by' => $filters['sort_by'] ?? 'latest',
            'planned_by' => $filters['planned_by'] ?? 0,
            'task_category_id' => $filters['task_category_id'] ?? 0,
            'has_schedule' => $filters['has_schedule'] ?? '',
            'start_date' => $filters['start_date_input'] ?? '',
            'end_date' => $filters['end_date_input'] ?? '',
        ];
    @endphp

    @include('partials.dashboard-nav', ['dashboardRoute' => $dashboardRoute, 'pageTitle' => __('texts.task_master_report')])

    <main class="app-card p-3 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <p class="text-light small mb-0">{{ __('texts.task_master_report_subtitle') }}</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#reportFilterPanel" aria-expanded="false" aria-controls="reportFilterPanel">
                    <i class="fas fa-filter"></i> {{ __('texts.filter') }}
                </button>
                <a href="{{ route('reports.task-masters.export', $queryForExport) }}" class="btn btn-app btn-sm">
                    <i class="fas fa-file-excel"></i> {{ __('texts.download_excel') }}
                </a>
            </div>
        </div>

        <div class="collapse report-filter-card" id="reportFilterPanel">
            <form method="GET" action="{{ route('reports.task-masters.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label for="keyword" class="form-label small text-light mb-1">{{ __('texts.keyword') }}</label>
                    <input type="text" id="keyword" name="keyword" class="form-control form-control-sm" value="{{ $filters['keyword'] ?? '' }}" placeholder="Code, task name, planner">
                </div>

                <div class="col-6 col-md-2">
                    <label for="status" class="form-label small text-light mb-1">{{ __('texts.task_status') }}</label>
                    <select id="status" name="status" class="form-select form-select-sm">
                        <option value="">{{ __('texts.report_status_all') }}</option>
                        <option value="0" {{ ($filters['status'] ?? '') === '0' ? 'selected' : '' }}>{{ __('texts.new_task') }}</option>
                        <option value="1" {{ ($filters['status'] ?? '') === '1' ? 'selected' : '' }}>{{ __('texts.on_progress') }}</option>
                        <option value="2" {{ ($filters['status'] ?? '') === '2' ? 'selected' : '' }}>{{ __('texts.done') }}</option>
                        <option value="3" {{ ($filters['status'] ?? '') === '3' ? 'selected' : '' }}>{{ __('texts.hold') }}</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label for="has_schedule" class="form-label small text-light mb-1">{{ __('texts.interval') }}</label>
                    <select id="has_schedule" name="has_schedule" class="form-select form-select-sm">
                        <option value="">{{ __('texts.report_schedule_all') }}</option>
                        <option value="1" {{ ($filters['has_schedule'] ?? '') === '1' ? 'selected' : '' }}>{{ __('texts.report_schedule_yes') }}</option>
                        <option value="0" {{ ($filters['has_schedule'] ?? '') === '0' ? 'selected' : '' }}>{{ __('texts.report_schedule_no') }}</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label for="start_date" class="form-label small text-light mb-1">{{ __('texts.start_date') }}</label>
                    <input type="date" id="start_date" name="start_date" class="form-control form-control-sm" value="{{ $filters['start_date_input'] ?? '' }}">
                </div>

                <div class="col-6 col-md-2">
                    <label for="end_date" class="form-label small text-light mb-1">{{ __('texts.end_date') }}</label>
                    <input type="date" id="end_date" name="end_date" class="form-control form-control-sm" value="{{ $filters['end_date_input'] ?? '' }}">
                </div>

                <div class="col-12 col-md-3">
                    <label for="task_category_id" class="form-label small text-light mb-1">{{ __('texts.task_categories') }}</label>
                    <select id="task_category_id" name="task_category_id" class="form-select form-select-sm">
                        <option value="0">{{ __('texts.all_category') }}</option>
                        @foreach ($taskCategories as $taskCategory)
                            <option value="{{ $taskCategory->id }}" {{ (int) ($filters['task_category_id'] ?? 0) === (int) $taskCategory->id ? 'selected' : '' }}>
                                {{ $taskCategory->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if ($isManager ?? false)
                    <div class="col-12 col-md-3">
                        <label for="planned_by" class="form-label small text-light mb-1">{{ __('texts.report_planner') }}</label>
                        <select id="planned_by" name="planned_by" class="form-select form-select-sm">
                            <option value="0">{{ __('texts.report_planner_all') }}</option>
                            @foreach ($adminUsers as $adminUser)
                                <option value="{{ $adminUser->id }}" {{ (int) ($filters['planned_by'] ?? 0) === (int) $adminUser->id ? 'selected' : '' }}>{{ $adminUser->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-6 col-md-2">
                    <label for="sort_by" class="form-label small text-light mb-1">{{ __('texts.sort_by') }}</label>
                    <select id="sort_by" name="sort_by" class="form-select form-select-sm">
                        <option value="latest" {{ ($filters['sort_by'] ?? 'latest') === 'latest' ? 'selected' : '' }}>{{ __('texts.latest') }}</option>
                        <option value="oldest" {{ ($filters['sort_by'] ?? 'latest') === 'oldest' ? 'selected' : '' }}>{{ __('texts.oldest') }}</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label for="per_page" class="form-label small text-light mb-1">{{ __('texts.show') }}</label>
                    <select id="per_page" name="per_page" class="form-select form-select-sm">
                        @foreach ([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" {{ (int) ($filters['perPage'] ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-app btn-sm w-100">{{ __('texts.apply') }}</button>
                    <a href="{{ route('reports.task-masters.index') }}" class="btn btn-outline-light btn-sm">{{ __('texts.reset') }}</a>
                </div>
            </form>
        </div>

        <section class="mb-3">
            <h2 class="h6 mb-2">{{ __('texts.report_metrics') }}</h2>
            <div class="report-grid">
                <article class="report-metric">
                    <div class="report-metric__label">{{ __('texts.report_total_tasks') }}</div>
                    <div class="report-metric__value">{{ number_format($metrics['total_tasks'] ?? 0) }}</div>
                </article>
                <article class="report-metric">
                    <div class="report-metric__label">{{ __('texts.report_total_details') }}</div>
                    <div class="report-metric__value">{{ number_format($metrics['total_details'] ?? 0) }}</div>
                </article>
                <article class="report-metric">
                    <div class="report-metric__label">{{ __('texts.report_completion_rate') }}</div>
                    <div class="report-metric__value">{{ number_format((float) ($metrics['completion_rate'] ?? 0), 1) }}%</div>
                </article>
                <article class="report-metric">
                    <div class="report-metric__label">{{ __('texts.report_realization_rate') }}</div>
                    <div class="report-metric__value">{{ number_format((float) ($metrics['realization_rate'] ?? 0), 1) }}%</div>
                </article>
            </div>
        </section>

        <section>
            <h2 class="h6 mb-2">{{ __('texts.report_table') }}</h2>
            <p class="small text-light-emphasis mb-2">{{ __('texts.report_table_caption') }}</p>

            <div class="report-table-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle report-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('texts.code') }}</th>
                                <th>{{ __('texts.report_task_name') }}</th>
                                <th>{{ __('texts.category') }}</th>
                                <th>{{ __('texts.report_planner') }}</th>
                                <th>{{ __('texts.task_status') }}</th>
                                <th>{{ __('texts.report_details_progress') }}</th>
                                <th>{{ __('texts.report_schedule_type') }}</th>
                                <th>{{ __('texts.report_planning_range') }}</th>
                                <th>{{ __('texts.report_realization_range') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tasks as $task)
                                @php
                                    $rowNo = $loop->iteration + ($tasks->currentPage() - 1) * $tasks->perPage();
                                    $detailsTotal = (int) ($task->details_count ?? 0);
                                    $detailsDone = (int) ($task->done_details_count ?? 0);
                                    $progress = $detailsTotal > 0 ? round(($detailsDone / $detailsTotal) * 100, 1) : 0;
                                    $statusLabel = match ((int) $task->status) {
                                        1 => __('texts.on_progress'),
                                        2 => __('texts.done'),
                                        3 => __('texts.hold'),
                                        default => __('texts.new_task'),
                                    };
                                    $statusClass = match ((int) $task->status) {
                                        1 => 'bg-warning text-dark',
                                        2 => 'bg-success',
                                        3 => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $rowNo }}</td>
                                    <td>{{ $task->code }}</td>
                                    <td>
                                        <a href="{{ route('task-masters.show', $task) }}" class="link-light text-decoration-none fw-semibold">
                                            {{ $task->name }}
                                        </a>
                                    </td>
                                    <td>{{ $task->category?->name ?: __('texts.uncategorized') }}</td>
                                    <td>{{ $task->planner?->name ?: __('texts.unknown_user') }}</td>
                                    <td><span class="badge badge-status {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                    <td class="report-progress">{{ $detailsDone }}/{{ $detailsTotal }} ({{ $progress }}%)</td>
                                    <td>{{ $task->has_schedule ? __('texts.scheduled') : __('texts.no_schedule') }}</td>
                                    <td>
                                        {{ optional($task->date_planning_start)->format('Y-m-d') ?: __('texts.none') }}
                                        <br>
                                        <span class="text-light-emphasis small">{{ optional($task->date_planning_finish)->format('Y-m-d') ?: __('texts.none') }}</span>
                                    </td>
                                    <td>
                                        @if ($task->date_realization_start || $task->date_realization_finish)
                                            {{ optional($task->date_realization_start)->format('Y-m-d') ?: __('texts.none') }}
                                            <br>
                                            <span class="text-light-emphasis small">{{ optional($task->date_realization_finish)->format('Y-m-d') ?: __('texts.none') }}</span>
                                        @else
                                            <span class="text-light-emphasis">{{ __('texts.report_no_realization') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-light-emphasis py-4">{{ __('texts.report_no_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3 d-flex justify-content-center">
                <div class="pagination-dark">
                    {{ $tasks->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </section>
    </main>
@endsection
