@extends('layouts.app')

@section('title', $mode === 'edit' ? __('texts.edit_document_category') : __('texts.create_document_category'))

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => $mode === 'edit' ? __('texts.edit_document_category') : __('texts.create_document_category')])

    <main class="app-card p-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <!-- <h1 class="h4 mb-1">{{ $mode === 'edit' ? 'Edit Category' : 'Create Category' }}</h1> -->
                <p class="text-light small mb-0">{{ __('texts.fill_category_form') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ $mode === 'edit' ? route('task-categories.update', $category) : route('task-categories.store') }}">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="form-label">{{ __('texts.code') }} <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $category->code) }}" required>
                @error('code') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('texts.name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('texts.description') }}</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $category->description) }}</textarea>
                @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="isActive">{{ __('texts.active') }}</label>
            </div>

            <div class="mt-4">
                <a href="{{ route('task-categories.index') }}" class="btn btn-outline-light">{{ __('texts.back') }}</a>
                <button type="submit" class="btn btn-app">{{ __('texts.save') }}</button>
            </div>
        </form>
    </main>
@endsection
