@extends('layouts.app')

@section('title', $mode === 'edit' ? __('texts.edit_user') : __('texts.create_user'))

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => $mode === 'edit' ? __('texts.edit_user') : __('texts.create_user')])

    <main class="app-card p-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-light small mb-0">{{ __('texts.fill_user_form') }}</p>
        </div>

        <form method="POST" action="{{ $mode === 'edit' ? route('base-users.update', $baseUser) : route('base-users.store') }}">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="form-label">{{ __('texts.name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $baseUser->name) }}" required>
                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('texts.email') }} <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $baseUser->email) }}" required>
                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            @if ($mode === 'create')
                <div class="mb-3">
                    <label class="form-label">{{ __('texts.password') }} <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" minlength="8" required>
                    @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('texts.confirm_password') }} <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label">{{ __('texts.role') }} <span class="text-danger">*</span></label>
                <select name="role" class="form-select" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role', $selectedRole) === $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
                @error('role') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('texts.department') }}</label>
                <select name="mst_department_id" class="form-select">
                    <option value="">{{ __('texts.select_department') }}</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" {{ (string) old('mst_department_id', $baseUser->profile?->mst_department_id) === (string) $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                    @endforeach
                </select>
                @error('mst_department_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('texts.date_of_birth') }}</label>
                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($baseUser->profile?->date_of_birth)->format('Y-m-d')) }}">
                @error('date_of_birth') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('texts.phone') }}</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $baseUser->profile?->phone) }}">
                @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('texts.address') }}</label>
                <textarea name="address" class="form-control" rows="3">{{ old('address', $baseUser->profile?->address) }}</textarea>
                @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mt-4">
                <a href="{{ route('base-users.index') }}" class="btn btn-outline-light">{{ __('texts.back') }}</a>
                <button type="submit" class="btn btn-app">{{ __('texts.save') }}</button>
            </div>
        </form>
    </main>
@endsection
