@extends('layouts.app')

@section('title', 'Change User Password')

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => 'Change User Password'])

    <main class="app-card p-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-light small mb-0">Set a new password for {{ $baseUser->name }}.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('base-users.password.update', $baseUser) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">New Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" minlength="8" required>
                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
            </div>

            <div class="mt-4">
                <a href="{{ route('base-users.index') }}" class="btn btn-outline-light">Back</a>
                <button type="submit" class="btn btn-app">Update Password</button>
            </div>
        </form>
    </main>
@endsection
