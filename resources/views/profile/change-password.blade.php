@extends('layouts.app')

@section('title', 'Change Password')

@php
    $dashboardRoute = auth()->user()->roles()->where('name', 'manager')->exists()
        ? route('manager.dashboard')
        : route('admin.dashboard');
@endphp

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => $dashboardRoute, 'pageTitle' => 'Change Password'])

    {{-- <header class="app-card p-4">
        <p class="text-uppercase small text-warning mb-1">Security</p>
        <h1 class="h3 mb-2">Change Password</h1>
        <p class="mb-0 text-light-emphasis">Gunakan password baru yang kuat agar akun tetap aman.</p>
    </header> --}}

    <main class="app-card p-4 flex-grow-1">
        @if (session('status'))
            <div class="alert alert-success py-2" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger py-2" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="d-grid gap-3">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="form-label">Current Password</label>
                <input
                    id="current_password"
                    name="current_password"
                    type="password"
                    class="form-control form-control-lg"
                    required
                >
            </div>

            <div>
                <label for="password" class="form-label">New Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="form-control form-control-lg"
                    minlength="8"
                    required
                >
            </div>

            <div>
                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    class="form-control form-control-lg"
                    minlength="8"
                    required
                >
            </div>

            <button type="submit" class="btn btn-app btn-lg w-100 mt-3">Update Password</button>
        </form>
    </main>
@endsection
