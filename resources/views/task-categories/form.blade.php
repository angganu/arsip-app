@extends('layouts.app')

@section('title', $mode === 'edit' ? 'Edit Document Category' : 'Create Document Category')

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard')])

    <main class="app-card p-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 mb-1">{{ $mode === 'edit' ? 'Edit Category' : 'Create Category' }}</h1>
                <p class="text-light-emphasis small mb-0">Fill in the form below to save the category.</p>
            </div>
            <a href="{{ route('task-categories.index') }}" class="btn btn-outline-light btn-sm">Back</a>
        </div>

        <form method="POST" action="{{ $mode === 'edit' ? route('task-categories.update', $category) : route('task-categories.store') }}">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="form-label">Code</label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $category->code) }}" placeholder="Optional">
                @error('code') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $category->description) }}</textarea>
                @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="isActive">Active</label>
            </div>

            <button type="submit" class="btn btn-app">Save</button>
        </form>
    </main>
@endsection
