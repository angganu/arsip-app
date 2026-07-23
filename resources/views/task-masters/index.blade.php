@extends('layouts.app')

@php
    $intervalLabels = [
        0 => __('texts.no_schedule'),
        1 => __('texts.day'),
        2 => __('texts.week'),
        3 => __('texts.month'),
        4 => __('texts.year'),
    ];
@endphp

@push('styles')
    <style>
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

        .task-card-list {
            display: grid;
            gap: 0.75rem;
        }

        .task-card {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.9rem;
            padding: 1rem;
        }

        .task-card__header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .task-card__title {
            font-weight: 600;
            color: #f8fafc;
            margin-bottom: 0.2rem;
        }

        .task-card__meta {
            font-size: 0.85rem;
            color: #cbd5e1;
        }

        .task-progress {
            --progress: 0;
            --ring-color: #64748b;
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: conic-gradient(var(--ring-color) calc(var(--progress) * 1%), rgba(148, 163, 184, 0.25) 0);
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.14) inset;
            flex-shrink: 0;
        }

        .task-progress__inner {
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: rgba(15, 23, 42, 0.95);
            color: #f8fafc;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            line-height: 1;
        }

        .task-card__grid {
            display: grid;
            gap: 0.5rem;
            color: #e2e8f0;
            font-size: 0.95rem;
        }

        .task-card__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.85rem;
            align-items: center;
        }

        .task-card__dropdown .dropdown-menu {
            background: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.15);
            min-width: 13rem;
        }

        .task-card__dropdown .dropdown-item {
            color: #e2e8f0;
        }

        .task-card__dropdown .dropdown-item:hover,
        .task-card__dropdown .dropdown-item:focus {
            color: #ffffff;
            background: rgba(59, 130, 246, 0.2);
        }

        .task-card__dropdown .dropdown-divider {
            border-color: rgba(255, 255, 255, 0.15);
        }

        .task-card__dropdown .dropdown-item-danger {
            color: #fca5a5;
        }

        .task-card__dropdown .dropdown-item-danger:hover,
        .task-card__dropdown .dropdown-item-danger:focus {
            color: #fecaca;
            background: rgba(220, 38, 38, 0.25);
        }

        .chat-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.25rem;
            height: 1.25rem;
            padding: 0 0.35rem;
            margin-left: 0.35rem;
            border-radius: 999px;
            background: #dc2626;
            color: #ffffff;
            font-size: 0.7rem;
            font-weight: 700;
            line-height: 1;
        }

        .filter-card {
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(15, 23, 42, 0.5);
            border-radius: 0.9rem;
            padding: 0.95rem;
            margin-bottom: 1rem;
        }

        @media (min-width: 768px) {
            .task-card-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
@endpush

@section('title', __('texts.document_list'))

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => __('texts.document_list')])

    <main class="app-card p-3 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <p class="text-light small mb-0">{{ __('texts.manage_document_planning') }}</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel" aria-expanded="false" aria-controls="filterPanel">
                    <i class="fas fa-filter"></i> {{ __('texts.filter') }}
                </button>
                <a href="{{ route('task-masters.create') }}" class="btn btn-app"><i class="fas fa-plus"></i> {{ __('texts.new') }}</a>
            </div>
        </div>

        <div class="collapse filter-card" id="filterPanel">
            <form method="GET" action="{{ route('task-masters.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label for="keyword" class="form-label small text-light mb-1">{{ __('texts.keyword') }}</label>
                    <input type="text" name="keyword" id="keyword" class="form-control form-control-sm" value="{{ old('keyword', $keyword ?? '') }}" placeholder="Code, name, description, category">
                </div>

                <div class="col-12 col-md-2">
                    <label for="status" class="form-label small text-light mb-1">{{ __('texts.task_status') }}</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">{{ __('texts.all') }}</option>
                        <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>{{ __('texts.new_task') }}</option>
                        <option value="1" {{ ($status ?? '') === '1' ? 'selected' : '' }}>{{ __('texts.on_progress') }}</option>
                        <option value="2" {{ ($status ?? '') === '2' ? 'selected' : '' }}>{{ __('texts.done') }}</option>
                        <option value="3" {{ ($status ?? '') === '3' ? 'selected' : '' }}>{{ __('texts.hold') }}</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label for="start_date" class="form-label small text-light mb-1">{{ __('texts.start_date') }}</label>
                    <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDateInput ?? '' }}">
                </div>

                <div class="col-6 col-md-2">
                    <label for="end_date" class="form-label small text-light mb-1">{{ __('texts.end_date') }}</label>
                    <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDateInput ?? '' }}">
                </div>

                <div class="col-12 col-md-3">
                    <label for="task_category_id" class="form-label small text-light mb-1">{{ __('texts.task_categories') }}</label>
                    <select name="task_category_id" id="task_category_id" class="form-select form-select-sm">
                        <option value="0">{{ __('texts.all_category') }}</option>
                        @foreach (($taskCategories ?? collect()) as $taskCategory)
                            <option value="{{ $taskCategory->id }}" {{ (int) ($taskCategoryId ?? 0) === (int) $taskCategory->id ? 'selected' : '' }}>
                                {{ $taskCategory->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if ($isManager ?? false)
                    <div class="col-12 col-md-3">
                        <label for="planned_by" class="form-label small text-light mb-1">{{ __('texts.planned_by') }}</label>
                        <select name="planned_by" id="planned_by" class="form-select form-select-sm">
                            <option value="0">{{ __('texts.all_administrator') }}</option>
                            @foreach (($adminUsers ?? collect()) as $adminUser)
                                <option value="{{ $adminUser->id }}" {{ (int) ($plannedBy ?? 0) === (int) $adminUser->id ? 'selected' : '' }}>{{ $adminUser->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-12 col-md-2">
                    <label for="sort_by" class="form-label small text-light mb-1">{{ __('texts.sort_by') }}</label>
                    <select name="sort_by" id="sort_by" class="form-select form-select-sm">
                        <option value="latest" {{ ($sortBy ?? 'latest') === 'latest' ? 'selected' : '' }}>{{ __('texts.latest') }}</option>
                        <option value="oldest" {{ ($sortBy ?? 'latest') === 'oldest' ? 'selected' : '' }}>{{ __('texts.oldest') }}</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-app btn-sm w-100">{{ __('texts.apply') }}</button>
                    <a href="{{ route('task-masters.index') }}" class="btn btn-outline-light btn-sm">{{ __('texts.reset') }}</a>
                </div>
            </form>
        </div>

        @if (session('success'))
            <div class="alert alert-success py-2 px-3 mb-3">{{ session('success') }}</div>
        @endif

        <div class="task-card-list">
            @forelse ($tasks as $task)
                @php
                    $totalDetails = (int) ($task->details_count ?? 0);
                    $doneDetails = (int) ($task->done_details_count ?? 0);
                    $unreadDiscussions = (int) ($task->unread_discussions_count ?? 0);
                    $progressPercent = $totalDetails > 0 ? (int) round(($doneDetails / $totalDetails) * 100) : 0;
                    $progressPercent = max(0, min(100, $progressPercent));
                    $progressColor = $progressPercent === 100 ? '#22c55e' : ($progressPercent > 0 ? '#3b82f6' : '#64748b');
                @endphp
                <div class="task-card">
                    <div class="task-card__header mb-0">
                        <div>
                            <div class="task-card__meta"><b>#{{ $loop->iteration + ($tasks->currentPage() - 1) * $tasks->perPage() }}</b> · {{ $task->code }}</div>
                            <div class="task-card__meta">{{ $task->category?->name ?: __('texts.no_category') }}</div>
                        </div>
                        <div class="task-progress"
                             style="--progress: {{ $progressPercent }}; --ring-color: {{ $progressColor }};"
                             role="img"
                             aria-label="Task progress {{ $progressPercent }} percent">
                            <div class="task-progress__inner">{{ $progressPercent }}%</div>
                        </div>
                    </div>

                    <div class="task-card__grid">
                        <div><strong>{{ $task->name }}</strong></div>
                        <div><strong>{{ __('texts.planning') }}:</strong> {{ optional($task->date_planning_start)->format('Y-m-d') ?: __('texts.none') }} to {{ optional($task->date_planning_finish)->format('Y-m-d') ?: __('texts.none') }}</div>
                        <div><strong>{{ __('texts.duration') }}:</strong> {{ $task->duration_planning ?? 0 }} {{ __('texts.day_suffix') }}</div>
                        <div><strong>{{ __('texts.interval') }}:</strong> {{ $task->has_schedule ? __('texts.every').' '. $task->interval_value.' '.$intervalLabels[$task->interval_schedule] : __('texts.no_schedule') }}</div>
                        <div class="text-light-emphasis small">{{ $task->description ?: __('texts.none') }}</div>
                    </div>

                    <hr>
                    <div class="task-card__actions">
                        <div class="dropdown task-card__dropdown">
                            <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('texts.action') }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a href="{{ route('task-masters.show', $task) }}" class="dropdown-item">{{ __('texts.view') }}</a></li>
                                <li><a href="{{ route('task-masters.edit', $task) }}" class="dropdown-item">{{ __('texts.manage') }}</a></li>
                                <li><a href="{{ route('task-masters.details.create', $task) }}" class="dropdown-item">{{ __('texts.task_detail_add_button') }}</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('task-masters.destroy', $task) }}" method="POST" data-confirm-message="{{ __('texts.confirm_delete', ['name' => $task->name]) }}" onsubmit="return confirm(this.dataset.confirmMessage)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item dropdown-item-danger">{{ __('texts.delete') }}</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                        <a href="{{ route('task-masters.discussion.index', $task) }}" class="btn btn-sm btn-outline-info ms-auto">
                            {{ __('texts.chat') }}
                            @if ($unreadDiscussions > 0)
                                <span class="chat-count">{{ $unreadDiscussions }}</span>
                            @endif
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center text-light-emphasis py-3">{{ __('texts.no_documents_found') }}</div>
            @endforelse
        </div>

        <div class="mt-3 d-flex justify-content-center">
            <div class="pagination-dark">
                {{ $tasks->appends(['per_page' => $perPage, 'keyword' => $keyword ?? '', 'status' => $status ?? '', 'sort_by' => $sortBy ?? 'latest', 'planned_by' => $plannedBy ?? 0, 'task_category_id' => $taskCategoryId ?? 0, 'start_date' => $startDateInput ?? '', 'end_date' => $endDateInput ?? ''])->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
@endsection