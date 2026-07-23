@extends('layouts.app')

@push('styles')
    <style>
        .pagination-dark .page-item .page-link {
            background-color: #1f2937;
            border-color: #374151;
            color: #f8fafc;
        }

        .pagination-dark .page-item .page-link:hover,
        .pagination-dark .page-item .page-link:focus {
            background-color: #374151;
            border-color: #4b5563;
            color: #ffffff;
        }

        .pagination-dark .page-item.active .page-link {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }

        .pagination-dark .page-item.disabled .page-link {
            background-color: #111827;
            border-color: #374151;
            color: #6b7280;
        }

        .user-card-list {
            display: grid;
            gap: 0.75rem;
        }

        .user-card {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.9rem;
            padding: 1rem;
        }

        .user-card__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .user-card__title {
            font-weight: 600;
            color: #f8fafc;
            margin-bottom: 0.2rem;
        }

        .user-card__meta {
            font-size: 0.85rem;
            color: #cbd5e1;
        }

        .user-card__body {
            display: grid;
            gap: 0.45rem;
            color: #e2e8f0;
            font-size: 0.95rem;
        }

        .user-card__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.85rem;
        }

        .filter-card {
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(15, 23, 42, 0.5);
            border-radius: 0.9rem;
            padding: 0.95rem;
            margin-bottom: 1rem;
        }

        @media (min-width: 768px) {
            .user-card-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
@endpush

@section('title', __('texts.users'))

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => __('texts.users')])

    <main class="app-card p-3 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-light small mb-0">{{ __('texts.manage_users') }}</p>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel" aria-expanded="false" aria-controls="filterPanel">
                    <i class="fas fa-filter"></i> {{ __('texts.filter') }}
                </button>
                <a href="{{ route('base-users.create') }}" class="btn btn-app"><i class="fas fa-plus"></i> {{ __('texts.new') }}</a>
            </div>
        </div>

        <div class="collapse filter-card" id="filterPanel">
            <form method="GET" action="{{ route('base-users.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="keyword" class="form-label small text-light mb-1">{{ __('texts.keyword') }}</label>
                    <input type="text" name="keyword" id="keyword" class="form-control form-control-sm" value="{{ old('keyword', $keyword ?? '') }}" placeholder="Name or email">
                </div>

                <div class="col-12 col-md-3">
                    <label for="role" class="form-label small text-light mb-1">{{ __('texts.role') }}</label>
                    <select name="role" id="role" class="form-select form-select-sm">
                        <option value="">{{ __('texts.all') }}</option>
                        @foreach ($roles as $roleOption)
                            <option value="{{ $roleOption->name }}" {{ ($role ?? '') === $roleOption->name ? 'selected' : '' }}>{{ ucfirst($roleOption->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label for="department_id" class="form-label small text-light mb-1">{{ __('texts.department') }}</label>
                    <select name="department_id" id="department_id" class="form-select form-select-sm">
                        <option value="">{{ __('texts.all') }}</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ (string) ($departmentId ?? '') === (string) $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-app btn-sm w-100">{{ __('texts.apply') }}</button>
                    <a href="{{ route('base-users.index') }}" class="btn btn-outline-light btn-sm">{{ __('texts.reset') }}</a>
                </div>
            </form>
        </div>

        @if (session('success'))
            <div class="alert alert-success py-2 px-3 mb-3">{{ session('success') }}</div>
        @endif

        @if ($errors->has('base_user'))
            <div class="alert alert-danger py-2 px-3 mb-3">{{ $errors->first('base_user') }}</div>
        @endif

        <div class="user-card-list">
            @forelse ($users as $user)
                <div class="user-card">
                    <div class="user-card__header mb-1">
                        <div>
                            <div class="user-card__meta"><b>#{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</b> · {{ $user->email }}</div>
                            <div class="user-card__title">{{ $user->name }}</div>
                        </div>
                        <span class="badge bg-primary-subtle text-primary-emphasis">{{ ucfirst($user->roles->pluck('name')->first() ?? '-') }}</span>
                    </div>

                    <div class="user-card__body">
                        <div><span class="text-light-emphasis">{{ __('texts.department') }}:</span> {{ $user->profile?->department?->name ?: '-' }}</div>
                        <div><span class="text-light-emphasis">{{ __('texts.phone') }}:</span> {{ $user->profile?->phone ?: '-' }}</div>
                    </div>
                    <hr>
                    <div class="user-card__actions">
                        <a href="{{ route('base-users.edit', $user) }}" class="btn btn-sm btn-outline-warning">{{ __('texts.edit') }}</a>
                        <a href="{{ route('base-users.password.edit', $user) }}" class="btn btn-sm btn-outline-info">{{ __('texts.change_password') }}</a>
                        <form action="{{ route('base-users.destroy', $user) }}" method="POST" data-confirm-message="{{ __('texts.confirm_delete', ['name' => $user->name]) }}" onsubmit="return confirm(this.dataset.confirmMessage)">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('texts.delete') }}</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center text-light-emphasis py-3">{{ __('texts.no_users_found') }}</div>
            @endforelse
        </div>

        <div class="mt-3 d-flex justify-content-center">
            <div class="pagination-dark">
                {{ $users->appends(['per_page' => $perPage, 'keyword' => $keyword ?? '', 'role' => $role ?? '', 'department_id' => $departmentId ?? '', 'sort_by' => $sortBy ?? 'latest'])->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
@endsection
