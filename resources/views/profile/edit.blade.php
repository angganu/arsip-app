@extends('layouts.app')

@section('title', 'Profile')

@php
    $dashboardRoute = auth()->user()->roles()->where('name', 'manager')->exists()
        ? route('manager.dashboard')
        : route('admin.dashboard');
@endphp

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => $dashboardRoute])

    {{-- <header class="app-card p-4">
        <p class="text-uppercase small text-info mb-1">Account</p>
        <h1 class="h3 mb-2">Profile</h1>
        <p class="mb-0 text-light-emphasis">Perbarui data profil akun Anda.</p>
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

        <form method="POST" action="{{ route('profile.update') }}" class="d-grid gap-3">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="form-label">Name</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="form-control form-control-lg"
                    value="{{ old('name', $user->name) }}"
                    required
                >
            </div>

            <div>
                <label for="email" class="form-label">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    class="form-control form-control-lg"
                    value="{{ old('email', $user->email) }}"
                    required
                >
            </div>

            <button type="submit" class="btn btn-app btn-lg w-100">Save Profile</button>
        </form>
    </main>
@endsection
