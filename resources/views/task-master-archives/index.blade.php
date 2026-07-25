@extends('layouts.app')

@section('title', __('texts.archive_files'))

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => $dashboardRoute, 'pageTitle' => __('texts.archive_files')])

    <main class="app-card p-3 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <p class="text-light small mb-0">{{ __('texts.archive_history_subtitle') }}</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success py-2 px-3 mb-3">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-dark table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('texts.task') }}</th>
                        <th>{{ __('texts.generated_by') }}</th>
                        <th>{{ __('texts.generated_at') }}</th>
                        <th>{{ __('texts.file_size') }}</th>
                        <th class="text-end">{{ __('texts.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($archives as $archive)
                        <tr>
                            <td>{{ $loop->iteration + ($archives->currentPage() - 1) * $archives->perPage() }}</td>
                            <td>
                                <div class="fw-semibold">{{ $archive->taskMaster?->name ?: __('texts.none') }}</div>
                                <div class="small text-light-emphasis">{{ $archive->taskMaster?->code ?: __('texts.none') }}</div>
                            </td>
                            <td>{{ $archive->generator?->name ?: __('texts.none') }}</td>
                            <td>{{ optional($archive->created_at)->format('Y-m-d H:i') ?: __('texts.none') }}</td>
                            <td>{{ (int) ($archive->size ?? 0) }} KB</td>
                            <td class="text-end">
                                <a href="{{ route('task-master-archives.download', $archive) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-download"></i> {{ __('texts.download') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-light-emphasis py-3">{{ __('texts.no_archive_files') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex justify-content-center">
            {{ $archives->links('pagination::bootstrap-4') }}
        </div>
    </main>
@endsection
