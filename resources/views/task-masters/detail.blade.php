@extends('layouts.app')

@section('title', 'Document Detail')

@push('styles')
    <style>
        .detail-grid {
            display: grid;
            gap: 1rem;
        }

        .detail-item {
            padding-bottom: 0.9rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .detail-item--full {
            grid-column: 1 / -1;
        }

        .detail-label {
            font-size: 0.8rem;
            color: rgba(226, 232, 240, 0.75);
            margin-bottom: 0.25rem;
        }

        .detail-value {
            color: #f8fafc;
            word-break: break-word;
        }

        @media (min-width: 768px) {
            .detail-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
@endpush

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => 'Document Detail'])

    <main class="app-card p-4 flex-grow-1">
        <!-- <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <p class="text-light small mb-0">View all saved values for this document.</p>
            </div>
        </div> -->

        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Code</div>
                <div class="detail-value">{{ $taskMaster->code ?: '—' }}</div>
            </div>

            <div class="detail-item detail-item--full">
                <div class="detail-label">Category</div>
                <div class="detail-value">{{ $taskMaster->category?->name ?: 'No category' }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Task Title</div>
                <div class="detail-value">{{ $taskMaster->name ?: '—' }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Planning Date</div>
                <div class="detail-value">{{ optional($taskMaster->date_planning_start)->format('Y-m-d') ?: '—' }} - {{ optional($taskMaster->date_planning_finish)->format('Y-m-d') ?: '—' }} ({{ $taskMaster->duration_planning ?? 0 }} days)</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Scheduled</div>
                <div class="detail-value">{{ $taskMaster->has_schedule ? 'Every '. $taskMaster->interval_value.' '.$intervalLabel : 'No schedule' }}</div>
            </div>

            <div class="detail-item detail-item--full">
                <div class="detail-label">Description</div>
                <div class="detail-value">{{ $taskMaster->description ?: '—' }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Planned By</div>
                <div class="detail-value">{{ $taskMaster->planned_by ?: '—' }}</div>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('task-masters.index') }}" class="btn btn-outline-light">Back</a>
            <!-- <a href="{{ route('task-masters.edit', $taskMaster) }}" class="btn btn-app">Edit</a> -->
        </div>
    </main>
@endsection