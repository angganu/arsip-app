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
    </style>
@endpush

@section('title', 'Document Categories')

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard')])

    <main class="app-card p-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 mb-1">Document Categories</h1>
                <p class="text-light-emphasis small mb-0">Manage task categories for document handling.</p>
            </div>
            <a href="{{ route('task-categories.create') }}" class="btn btn-app">Create New</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success py-2 px-3 mb-3">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('task-categories.index') }}" class="d-flex align-items-center gap-2 mb-3">
            <label for="per_page" class="form-label mb-0 small text-light-emphasis">Rows per page</label>
            <select name="per_page" id="per_page" class="form-select form-select-sm w-auto bg-dark text-white border-secondary" onchange="this.form.submit()">
                @foreach ([10, 25, 50, 100] as $size)
                    <option value="{{ $size }}" {{ ($perPage ?? 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </select>
        </form>

        <div class="table-responsive">
            <table class="table table-dark table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                            <td>{{ $category->code }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->description }}</td>
                            <td>
                                <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('task-categories.edit', $category) }}" class="btn btn-sm btn-outline-light">Edit</a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal-{{ $category->id }}">
                                        Delete
                                    </button>

                                    <div class="modal fade" id="deleteCategoryModal-{{ $category->id }}" tabindex="-1" aria-labelledby="deleteCategoryModalLabel-{{ $category->id }}" aria-hidden="true" data-bs-backdrop="false">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content bg-dark text-white">
                                                <div class="modal-header border-secondary">
                                                    <h5 class="modal-title" id="deleteCategoryModalLabel-{{ $category->id }}">Delete Category</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-0">Are you sure you want to delete <strong>{{ $category->name }}</strong>?</p>
                                                </div>
                                                <div class="modal-footer border-secondary">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('task-categories.destroy', $category) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-light-emphasis">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex justify-content-center">
            <div class="pagination-dark">
                {{ $categories->appends(['per_page' => $perPage])->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
@endsection
