@extends('layouts.app')

@section('title', __('texts.change_user_password'))

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
                <div class="password-field-wrap">
                    <input type="password" id="password" name="password" class="form-control" minlength="8" required>
                    <button type="button" class="password-toggle" data-target="password" aria-label="Show password">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('texts.confirm_password') }} <span class="text-danger">*</span></label>
                <div class="password-field-wrap">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" minlength="8" required>
                    <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="Show password">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('base-users.index') }}" class="btn btn-outline-light">{{ __('texts.back') }}</a>
                <button type="submit" class="btn btn-app">{{ __('texts.update_password') }}</button>
            </div>
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
