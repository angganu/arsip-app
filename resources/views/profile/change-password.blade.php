@extends('layouts.app')

@section('title', __('texts.change_password'))

@php
    $dashboardRoute = auth()->user()->roles()->where('name', 'manager')->exists()
        ? route('manager.dashboard')
        : route('admin.dashboard');
@endphp

@push('styles')
    <style>
        .password-field-wrap {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #94a3b8;
            padding: 0;
            line-height: 1;
        }

        .password-toggle:hover,
        .password-toggle:focus {
            color: #e2e8f0;
        }

        .password-field-wrap .form-control {
            padding-right: 2.5rem;
        }
    </style>
@endpush

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => $dashboardRoute, 'pageTitle' => __('texts.change_password')])

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
                <label for="current_password" class="form-label">{{ __('texts.current_password') }}</label>
                <div class="password-field-wrap">
                    <input
                        id="current_password"
                        name="current_password"
                        type="password"
                        class="form-control form-control-lg"
                        required
                    >
                    <button type="button" class="password-toggle" data-target="current_password" aria-label="Show password">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div>
                <label for="password" class="form-label">{{ __('texts.new_password') }}</label>
                <div class="password-field-wrap">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="form-control form-control-lg"
                        minlength="8"
                        required
                    >
                    <button type="button" class="password-toggle" data-target="password" aria-label="Show password">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="form-label">{{ __('texts.confirm_new_password') }}</label>
                <div class="password-field-wrap">
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="form-control form-control-lg"
                        minlength="8"
                        required
                    >
                    <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="Show password">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-app btn-lg w-100 mt-3">{{ __('texts.update_password') }}</button>
        </form>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.password-toggle').forEach(function (toggleButton) {
                toggleButton.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');

                    if (!input || !icon) {
                        return;
                    }

                    const isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    icon.classList.toggle('fa-eye', !isHidden);
                    icon.classList.toggle('fa-eye-slash', isHidden);
                    this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                });
            });
        });
    </script>
@endpush
