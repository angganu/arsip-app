@extends('layouts.app')

@section('title', __('texts.profile'))

@php
    $dashboardRoute = auth()->user()->roles()->where('name', 'manager')->exists()
        ? route('manager.dashboard')
        : route('admin.dashboard');
@endphp

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => $dashboardRoute, 'pageTitle' => __('texts.profile')])

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

        <form method="POST" action="{{ route('profile.update') }}" class="d-grid gap-3" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @if ($user->profile?->avatar_path)
                <div class="text-center">
                    <img
                        src="{{ asset('storage/' . $user->profile->avatar_path) }}"
                        alt="{{ __('texts.current_avatar') }}"
                        class="rounded-circle border border-light border-opacity-50"
                        style="width: 88px; height: 88px; object-fit: cover;"
                    >
                </div>
            @endif

            <div>
                <label for="avatar" class="form-label">{{ __('texts.avatar') }}</label>
                <input
                    id="avatar"
                    name="avatar"
                    type="file"
                    class="form-control form-control-lg"
                    accept="image/*"
                >
            </div>

            <div>
                <label for="name" class="form-label">{{ __('texts.name') }}</label>
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
                <label for="email" class="form-label">{{ __('texts.email') }}</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    class="form-control form-control-lg"
                    value="{{ old('email', $user->email) }}"
                    required
                >
            </div>

            <div>
                <label for="date_of_birth" class="form-label">{{ __('texts.date_of_birth') }}</label>
                <input
                    id="date_of_birth"
                    name="date_of_birth"
                    type="date"
                    class="form-control form-control-lg"
                    value="{{ old('date_of_birth', optional($user->profile?->date_of_birth)->format('Y-m-d')) }}"
                >
            </div>

            <div>
                <label for="phone" class="form-label">{{ __('texts.phone') }}</label>
                <input
                    id="phone"
                    name="phone"
                    type="text"
                    class="form-control form-control-lg"
                    value="{{ old('phone', $user->profile?->phone) }}"
                >
            </div>

            <div>
                <label for="address" class="form-label">{{ __('texts.address') }}</label>
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    class="form-control form-control-lg"
                >{{ old('address', $user->profile?->address) }}</textarea>
            </div>

            <div>
                <label for="mst_department_id" class="form-label">{{ __('texts.department') }}</label>
                <select id="mst_department_id" name="mst_department_id" class="form-select form-select-lg">
                    <option value="">{{ __('texts.select_department') }}</option>
                    @foreach ($departments as $department)
                        <option
                            value="{{ $department->id }}"
                            @selected(old('mst_department_id', $user->profile?->mst_department_id) == $department->id)
                        >
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-app btn-lg w-100 mt-3">{{ __('texts.save_profile') }}</button>
        </form>
    </main>
@endsection
