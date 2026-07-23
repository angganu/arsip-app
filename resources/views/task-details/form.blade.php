@extends('layouts.app')

@section('title', __('texts.task_detail_add_title'))

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => __('texts.task_detail_add_title')])

    <main class="app-card p-4 flex-grow-1">
        <!-- <div class="mb-3">
            <p class="text-light small mb-0">Tambahkan rincian tugas untuk dokumen berikut.</p>
        </div> -->

        <div class="mb-3">
            <div class="text-light-emphasis small">{{ $taskMaster->code }}</div>
            <div class="text-light"><strong>{{ __('texts.task_label') }}:</strong> {{ $taskMaster->name }}</div>
        </div>

        <form method="POST" action="{{ route('task-masters.details.store', $taskMaster) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">{{ __('texts.activity') }} <span class="text-danger">*</span></label>
                <input type="text" name="activity" class="form-control" value="{{ old('activity') }}" required>
                @error('activity') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6 col-md-6">
                    <label class="form-label">{{ __('texts.planning_start') }} <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="date_planning_start" class="form-control" value="{{ old('date_planning_start') }}" required>
                    @error('date_planning_start') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-6 col-md-6">
                    <label class="form-label">{{ __('texts.planning_finish') }} <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="date_planning_finish" class="form-control" value="{{ old('date_planning_finish') }}" required>
                    @error('date_planning_finish') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('texts.description') }}</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mt-4 d-flex gap-2">
                <a href="{{ route('task-masters.index') }}" class="btn btn-outline-light">{{ __('texts.back') }}</a>
                <button type="submit" class="btn btn-app">{{ __('texts.save_detail') }}</button>
            </div>
        </form>
    </main>
@endsection
