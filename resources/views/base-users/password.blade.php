@extends('layouts.app')

@section('title', __('texts.change_user_password'))

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => __('texts.change_user_password')])

    <main class="app-card p-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-light small mb-0">{{ __('texts.set_new_password_for_user', ['name' => $baseUser->name]) }}</p>
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
                <label class="form-label">{{ __('texts.new_password') }} <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" minlength="8" required>
                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('texts.confirm_password') }} <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
            </div>

            <div class="mt-4">
                <a href="{{ route('base-users.index') }}" class="btn btn-outline-light">{{ __('texts.back') }}</a>
                <button type="submit" class="btn btn-app">{{ __('texts.update_password') }}</button>
            </div>
        </form>
    </main>
@endsection
