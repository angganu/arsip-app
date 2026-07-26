@extends('layouts.app')

@section('title', __('texts.archive_files'))

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => $dashboardRoute, 'pageTitle' => __('texts.archive_files')])

    <main class="app-card p-3 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <p class="text-light small mb-0">{{ __('texts.archive_history_subtitle') }}</p>
            </div>
        </div>

        <div class="border rounded p-3 mb-3" style="border-color: rgba(255, 255, 255, 0.12) !important; background: rgba(15, 23, 42, 0.5);">
            <form method="GET" action="{{ route('task-master-archives.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label for="keyword" class="form-label small text-light mb-1">{{ __('texts.keyword') }}</label>
                    <input type="text" name="keyword" id="keyword" class="form-control form-control-sm" value="{{ old('keyword', $keyword ?? '') }}" placeholder="Code or name">
                </div>

                <div class="col-6 col-md-2">
                    <label for="planning_start_date" class="form-label small text-light mb-1">Planning start</label>
                    <input type="date" name="planning_start_date" id="planning_start_date" class="form-control form-control-sm" value="{{ $planningStartDateInput ?? '' }}">
                </div>

                <div class="col-6 col-md-2">
                    <label for="planning_end_date" class="form-label small text-light mb-1">Planning finish</label>
                    <input type="date" name="planning_end_date" id="planning_end_date" class="form-control form-control-sm" value="{{ $planningEndDateInput ?? '' }}">
                </div>

                <div class="col-6 col-md-2">
                    <label for="realization_start_date" class="form-label small text-light mb-1">Realization start</label>
                    <input type="date" name="realization_start_date" id="realization_start_date" class="form-control form-control-sm" value="{{ $realizationStartDateInput ?? '' }}">
                </div>

                <div class="col-6 col-md-2">
                    <label for="realization_end_date" class="form-label small text-light mb-1">Realization finish</label>
                    <input type="date" name="realization_end_date" id="realization_end_date" class="form-control form-control-sm" value="{{ $realizationEndDateInput ?? '' }}">
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

                <div class="col-12 col-md-3">
                    <label for="planned_by" class="form-label small text-light mb-1">{{ __('texts.planned_by') }}</label>
                    <select name="planned_by" id="planned_by" class="form-select form-select-sm">
                        @if ($isManager ?? false)
                            <option value="0">{{ __('texts.all_administrator') }}</option>
                        @endif
                        @foreach (($adminUsers ?? collect()) as $adminUser)
                            <option value="{{ $adminUser->id }}" {{ (int) ($plannedBy ?? 0) === (int) $adminUser->id ? 'selected' : '' }}>{{ $adminUser->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100">{{ __('texts.apply') }}</button>
                    <a href="{{ route('task-master-archives.index') }}" class="btn btn-outline-light btn-sm">{{ __('texts.reset') }}</a>
                </div>
            </form>
        </div>

        @if (session('success'))
            <div class="alert alert-success py-2 px-3 mb-3">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-dark table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('texts.task') }}</th>
                        <th>{{ __('texts.generated_by') }}</th>
                        <th>{{ __('texts.generated_at') }}</th>
                        <th>{{ __('texts.file_size') }}</th>
                        <th class="text-end">{{ __('texts.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($archives as $archive)
                        <tr>
                            <td>{{ $loop->iteration + ($archives->currentPage() - 1) * $archives->perPage() }}</td>
                            <td>
                                <div class="fw-semibold">{{ $archive->taskMaster?->name ?: __('texts.none') }}</div>
                                <div class="small text-light-emphasis">{{ $archive->taskMaster?->code ?: __('texts.none') }}</div>
                            </td>
                            <td>{{ $archive->generator?->name ?: __('texts.none') }}</td>
                            <td>{{ optional($archive->created_at)->format('Y-m-d H:i') ?: __('texts.none') }}</td>
                            <td>{{ (int) ($archive->size ?? 0) }} KB</td>
                            <td class="text-end">
                                <a href="{{ route('task-master-archives.download', $archive) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-download"></i> {{ __('texts.download') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-light-emphasis py-3">{{ __('texts.no_archive_files') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex justify-content-center">
            {{ $archives->appends(['keyword' => $keyword ?? '', 'planning_start_date' => $planningStartDateInput ?? '', 'planning_end_date' => $planningEndDateInput ?? '', 'realization_start_date' => $realizationStartDateInput ?? '', 'realization_end_date' => $realizationEndDateInput ?? '', 'task_category_id' => $taskCategoryId ?? 0, 'planned_by' => $plannedBy ?? 0])->links('pagination::bootstrap-4') }}
        </div>
    </main>
@endsection
