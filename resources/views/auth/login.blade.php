@extends('layouts.app')

@section('title', __('texts.login') . ' - ' . __('texts.app_name'))

@section('content')
    <main class="d-flex flex-column justify-content-center flex-grow-1">
        <section class="app-card p-4 p-sm-4">
            <p class="text-uppercase small mb-1 text-info fw-semibold">Arsip App</p>
            <h1 class="h3 mb-2">{{ __('texts.login_to_account') }}</h1>
            <p class="text-light-emphasis mb-4">{{ __('texts.access_dashboard_by_role') }}</p>

            @if ($errors->any())
                <div class="alert alert-danger py-2" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.attempt') }}" method="POST" class="d-grid gap-3">
                @csrf

                <div>
                    <label for="email" class="form-label">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="form-control form-control-lg"
                        placeholder="you@example.com"
                    >
                </div>

                <div>
                    <label for="password" class="form-label">{{ __('texts.password') }}</label>
                    <div class="position-relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            class="form-control form-control-lg pe-5"
                            placeholder="{{ __('texts.enter_password') }}"
                        >
                        <button
                            type="button"
                            class="btn btn-link position-absolute top-50 end-0 translate-middle-y me-2 p-0 text-light-emphasis"
                            data-password-toggle
                            aria-label="Show password"
                        >
                            <svg class="password-toggle-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M8 13c-3.5 0-6-3.2-6-5 0-1.8 2.5-5 6-5s6 3.2 6 5c0 1.8-2.5 5-6 5m0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember" value="1">
                    <label class="form-check-label" for="remember">{{ __('texts.remember_me') }}</label>
                </div>

                <button type="submit" class="btn btn-app btn-lg w-100">{{ __('texts.login') }}</button>
            </form>
        </section>

        <section class="app-card p-3 mt-3">
            <p class="mb-1 fw-semibold">{{ __('texts.seed_sample_accounts') }}</p>
            <p class="mb-1 small text-light-emphasis">manager@example.com / password123</p>
            <p class="mb-0 small text-light-emphasis">admin@example.com / password123</p>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleButton = document.querySelector('[data-password-toggle]');

            if (!toggleButton) {
                return;
            }

            toggleButton.addEventListener('click', function () {
                const passwordInput = document.getElementById('password');
                const isPasswordHidden = passwordInput.type === 'password';

                passwordInput.type = isPasswordHidden ? 'text' : 'password';
                toggleButton.setAttribute('aria-label', isPasswordHidden ? 'Hide password' : 'Show password');
            });
        });
    </script>
@endsection
