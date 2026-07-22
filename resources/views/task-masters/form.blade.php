@extends('layouts.app')

@section('title', $mode === 'edit' ? 'Edit Document' : 'Create Document')

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => $mode === 'edit' ? 'Edit Document' : 'Create Document'])

    <main class="app-card p-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <p class="text-light small mb-0">Fill in the form below to save the document.</p>
            </div>
        </div>

        <form method="POST" action="{{ $mode === 'edit' ? route('task-masters.update', $taskMaster) : route('task-masters.store') }}">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select name="task_category_id" class="form-select" required>
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) old('task_category_id', $taskMaster->task_category_id) === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('task_category_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Task Title <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $taskMaster->name) }}" required>
                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6 col-md-6">
                    <label class="form-label">Planning Start <span class="text-danger">*</span></label>
                    <input type="date" name="date_planning_start" class="form-control" value="{{ old('date_planning_start', optional($taskMaster->date_planning_start)->format('Y-m-d')) }}" required>
                    @error('date_planning_start') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-6 col-md-6">
                    <label class="form-label">Planning Finish <span class="text-danger">*</span></label>
                    <input type="date" name="date_planning_finish" class="form-control" value="{{ old('date_planning_finish', optional($taskMaster->date_planning_finish)->format('Y-m-d')) }}" required>
                    @error('date_planning_finish') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="has_schedule" value="1" id="hasSchedule" {{ old('has_schedule', $taskMaster->has_schedule ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="hasSchedule">Has Schedule</label>
            </div>

            <div id="scheduleFields" class="row g-3 mb-3 {{ old('has_schedule', $taskMaster->has_schedule ?? false) ? '' : 'd-none' }}">
                <div class="col-6 col-md-6">
                    <label class="form-label">Interval Value <span class="text-danger">*</span></label>
                    <input type="number" min="1" name="interval_value" class="form-control" value="{{ old('interval_value', $taskMaster->interval_value ?: 1) }}">
                    @error('interval_value') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-6 col-md-6">
                    <label class="form-label">Interval Schedule <span class="text-danger">*</span></label>
                    <select name="interval_schedule" class="form-select">
                        <option value="">Select interval</option>
                        @foreach ($intervalOptions as $intervalOption)
                            <option value="{{ $intervalOption }}" {{ old('interval_schedule', $selectedInterval) === $intervalOption ? 'selected' : '' }}>{{ ucfirst($intervalOption) }}</option>
                        @endforeach
                    </select>
                    @error('interval_schedule') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $taskMaster->description) }}</textarea>
                @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mt-4">
                <a href="{{ route('task-masters.index') }}" class="btn btn-outline-light">Back</a>
                <button type="submit" class="btn btn-app">Save</button>
            </div>
        </form>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const hasScheduleInput = document.getElementById('hasSchedule');
            const scheduleFields = document.getElementById('scheduleFields');
            if (!hasScheduleInput || !scheduleFields) {
                return;
            }

            const toggleScheduleFields = function () {
                scheduleFields.classList.toggle('d-none', !hasScheduleInput.checked);
            };

            hasScheduleInput.addEventListener('change', toggleScheduleFields);
            toggleScheduleFields();
        });
    </script>
@endpush