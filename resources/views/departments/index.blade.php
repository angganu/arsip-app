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

        .department-card-list {
            display: grid;
            gap: 0.75rem;
        }

        .department-card {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.9rem;
            padding: 1rem;
        }

        .department-card__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .department-card__title {
            font-weight: 600;
            color: #f8fafc;
            margin-bottom: 0.2rem;
        }

        .department-card__meta {
            font-size: 0.85rem;
            color: #cbd5e1;
        }

        .department-card__actions {
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
            .department-card-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
@endpush

@section('title', 'Departments')

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => 'Departments'])

    <main class="app-card p-3 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-light small mb-0">Manage department master data.</p>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel" aria-expanded="false" aria-controls="filterPanel">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('departments.create') }}" class="btn btn-app"><i class="fas fa-plus"></i> New</a>
            </div>
        </div>

        <div class="collapse filter-card" id="filterPanel">
            <form method="GET" action="{{ route('departments.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label for="keyword" class="form-label small text-light mb-1">Keyword</label>
                    <input type="text" name="keyword" id="keyword" class="form-control form-control-sm" value="{{ old('keyword', $keyword ?? '') }}" placeholder="Code or name">
                </div>

                <div class="col-12 col-md-4">
                    <label for="sort_by" class="form-label small text-light mb-1">Sort by</label>
                    <select name="sort_by" id="sort_by" class="form-select form-select-sm">
                        <option value="latest" {{ ($sortBy ?? 'latest') === 'latest' ? 'selected' : '' }}>Latest</option>
                        <option value="oldest" {{ ($sortBy ?? 'latest') === 'oldest' ? 'selected' : '' }}>Oldest</option>
                        <option value="name_asc" {{ ($sortBy ?? 'latest') === 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                        <option value="name_desc" {{ ($sortBy ?? 'latest') === 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-app btn-sm w-100">Apply</button>
                    <a href="{{ route('departments.index') }}" class="btn btn-outline-light btn-sm">Reset</a>
                </div>
            </form>
        </div>

        @if (session('success'))
            <div class="alert alert-success py-2 px-3 mb-3">{{ session('success') }}</div>
        @endif

        @if ($errors->has('department'))
            <div class="alert alert-danger py-2 px-3 mb-3">{{ $errors->first('department') }}</div>
        @endif

        <div class="department-card-list">
            @forelse ($departments as $department)
                <div class="department-card">
                    <div class="department-card__header mb-1">
                        <div>
                            <div class="department-card__meta"><b>#{{ $loop->iteration + ($departments->currentPage() - 1) * $departments->perPage() }}</b> · {{ $department->code }}</div>
                            <div class="department-card__title">{{ $department->name }}</div>
                        </div>
                        <span class="badge bg-info-subtle text-info-emphasis">{{ $department->user_profiles_count }} users</span>
                    </div>

                    <hr>
                    <div class="department-card__actions">
                        <a href="{{ route('departments.edit', $department) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                        <form action="{{ route('departments.destroy', $department) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete {{ addslashes($department->name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center text-light-emphasis py-3">No departments found.</div>
            @endforelse
        </div>

        <div class="mt-3 d-flex justify-content-center">
            <div class="pagination-dark">
                {{ $departments->appends(['per_page' => $perPage, 'keyword' => $keyword ?? '', 'sort_by' => $sortBy ?? 'latest'])->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
@endsection
