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

        .category-card-list {
            display: grid;
            gap: 0.75rem;
        }

        .category-card {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.9rem;
            padding: 1rem;
        }

        .category-card__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .category-card__title {
            font-weight: 600;
            color: #f8fafc;
            margin-bottom: 0.2rem;
        }

        .category-card__meta {
            font-size: 0.85rem;
            color: #cbd5e1;
        }

        .category-card__body {
            display: grid;
            gap: 0.45rem;
            color: #e2e8f0;
            font-size: 0.95rem;
        }

        .category-card__actions {
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
            .category-card-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
@endpush

@section('title', __('texts.document_categories'))

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => __('texts.document_categories')])

    <main class="app-card p-3 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <p class="text-light small mb-0">{{ __('texts.manage_categories') }}</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel" aria-expanded="false" aria-controls="filterPanel">
                    <i class="fas fa-filter"></i> {{ __('texts.filter') }}
                </button>
                <a href="{{ route('task-categories.create') }}" class="btn btn-app"><i class="fas fa-plus"></i> {{ __('texts.new') }}</a>
            </div>
        </div>

        <div class="collapse filter-card" id="filterPanel">
            <form method="GET" action="{{ route('task-categories.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="keyword" class="form-label small text-light mb-1">{{ __('texts.keyword') }}</label>
                    <input type="text" name="keyword" id="keyword" class="form-control form-control-sm" value="{{ old('keyword', $keyword ?? '') }}" placeholder="Code, name, description">
                </div>

                <div class="col-12 col-md-3">
                    <label for="status" class="form-label small text-light mb-1">{{ __('texts.status') }}</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">{{ __('texts.all') }}</option>
                        <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>{{ __('texts.active') }}</option>
                        <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>{{ __('texts.inactive') }}</option>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label for="sort_by" class="form-label small text-light mb-1">{{ __('texts.sort_by') }}</label>
                    <select name="sort_by" id="sort_by" class="form-select form-select-sm">
                        <option value="latest" {{ ($sortBy ?? 'latest') === 'latest' ? 'selected' : '' }}>{{ __('texts.latest') }}</option>
                        <option value="oldest" {{ ($sortBy ?? 'latest') === 'oldest' ? 'selected' : '' }}>{{ __('texts.oldest') }}</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-app btn-sm w-100">{{ __('texts.apply') }}</button>
                    <a href="{{ route('task-categories.index') }}" class="btn btn-outline-light btn-sm">{{ __('texts.reset') }}</a>
                </div>
            </form>
        </div>

        @if (session('success'))
            <div class="alert alert-success py-2 px-3 mb-3">{{ session('success') }}</div>
        @endif

        <!-- <form method="GET" action="{{ route('task-categories.index') }}" class="d-flex align-items-center gap-2 mb-3">
            <label for="per_page" class="form-label mb-0 small text-light-emphasis">Rows per page</label>
            <select name="per_page" id="per_page" class="form-select form-select-sm w-auto bg-dark text-white border-secondary" onchange="this.form.submit()">
                @foreach ([10, 25, 50, 100] as $size)
                    <option value="{{ $size }}" {{ ($perPage ?? 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </select>
        </form> -->

        <div class="category-card-list">
            @forelse ($categories as $category)
                <div class="category-card">
                    <div class="category-card__header mb-1">
                        <div>
                            <div class="category-card__meta"><b>#{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</b> · {{ $category->code }}</div>
                            <div class="category-card__title">{{ $category->name }}</div>
                        </div>
                        <span class="badge-status badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $category->is_active ? __('texts.active') : __('texts.inactive') }}
                        </span>
                    </div>

                    <div class="category-card__body">
                        <div class="text-light-emphasis small mb-0">{{ $category->description ?: __('texts.none') }}</div>
                    </div>
                    <hr>
                    <div class="category-card__actions">
                        <a href="{{ route('task-categories.edit', $category) }}" class="btn btn-sm btn-outline-warning">{{ __('texts.edit') }}</a>
                        <form action="{{ route('task-categories.destroy', $category) }}" method="POST" onsubmit="return confirm({{ Illuminate\Support\Js::from(__('texts.confirm_delete', ['name' => $category->name])) }})">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('texts.delete') }}</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center text-light-emphasis py-3">{{ __('texts.no_categories_found') }}</div>
            @endforelse
        </div>

        <div class="mt-3 d-flex justify-content-center">
            <div class="pagination-dark">
                {{ $categories->appends(['per_page' => $perPage, 'keyword' => $keyword ?? '', 'status' => $status ?? '', 'sort_by' => $sortBy ?? 'latest'])->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
@endsection
