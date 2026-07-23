@extends('layouts.app')

@section('title', $mode === 'edit' ? __('texts.edit_department') : __('texts.create_department'))

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => $mode === 'edit' ? __('texts.edit_department') : __('texts.create_department')])

    <main class="app-card p-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-light small mb-0">{{ __('texts.fill_department_form') }}</p>
        </div>

        <form method="POST" action="{{ $mode === 'edit' ? route('departments.update', $department) : route('departments.store') }}">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="form-label">{{ __('texts.code') }}</label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $department->code) }}" placeholder="{{ __('texts.auto_generated_if_empty') }}">
                @error('code') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('texts.name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $department->name) }}" required>
                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mt-4">
                <a href="{{ route('departments.index') }}" class="btn btn-outline-light">{{ __('texts.back') }}</a>
                <button type="submit" class="btn btn-app">{{ __('texts.save') }}</button>
            </div>
        </form>
    </main>
@endsection
