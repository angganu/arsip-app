@extends('layouts.app')

@section('title', 'Document Detail')

@push('styles')
    <style>
        .detail-grid {
            display: grid;
            gap: 1rem;
        }

        .detail-section {
            margin-top: 2rem;
        }

        .detail-section-title {
            color: #f8fafc;
            font-size: 1rem;
            margin-bottom: 1rem;
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

        .detail-card-list {
            display: grid;
            gap: 1rem;
        }

        .detail-card {
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 0.85rem;
            background: rgba(2, 6, 23, 0.2);
            padding: 1rem;
        }

        .attachment-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1rem;
        }

        .attachment-preview-card {
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 0.85rem;
            background: rgba(2, 6, 23, 0.2);
            overflow: hidden;
        }

        .attachment-preview-link {
            display: block;
            text-decoration: none;
        }

        .attachment-preview-image {
            width: 100%;
            height: 140px;
            object-fit: cover;
            display: block;
            background: rgba(15, 23, 42, 0.6);
        }

        .attachment-preview-body {
            padding: 0.75rem;
        }

        .attachment-preview-name {
            color: #f8fafc;
            font-size: 0.8rem;
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

        <section class="detail-section">
            <h2 class="detail-section-title">Task Details ({{ $taskMaster->details->count() }})</h2>

            @if ($taskMaster->details->isEmpty())
                <div class="detail-value">No task details available.</div>
            @else
                <div class="detail-card-list">
                    @foreach ($taskMaster->details as $index => $detail)
                        <div class="detail-card">
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <div class="detail-label">Activity Code</div>
                                    <div class="detail-value">{{ $detail->code ?: '—' }}</div>
                                </div>

                                <div class="detail-item detail-item--full">
                                    <div class="detail-label">Activity Name</div>
                                    <div class="detail-value">{{ $detail->activity ?: '—' }}</div>
                                </div>

                                <div class="detail-item">
                                    <div class="detail-label">Date Planning</div>
                                    <div class="detail-value">{{ optional($detail->date_planning_start)->format('Y-m-d H:i') ?: '—' }} - {{ optional($detail->date_planning_finish)->format('Y-m-d H:i') ?: '—' }} ({{ $detail->duration_planning ?? 0 }} hours)</div>
                                </div>

                                <div class="detail-item detail-item--full">
                                    <div class="detail-label">Description</div>
                                    <div class="detail-value">{{ $detail->description ?: '—' }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="detail-section">
            <h2 class="detail-section-title">Task Attachments ({{ $taskMaster->attachments->count() }})</h2>

            @if ($taskMaster->attachments->isEmpty())
                <div class="detail-value">No attachments available.</div>
            @else
                <div class="attachment-preview-grid">
                    @foreach ($taskMaster->attachments as $attachment)
                        <div class="attachment-preview-card">
                            <a href="{{ route('task-attachments.preview', $attachment) }}" target="_blank" rel="noopener noreferrer" class="attachment-preview-link">
                                <img src="{{ route('task-attachments.preview', $attachment) }}" alt="{{ $attachment->original_name ?: $attachment->name }}" class="attachment-preview-image">
                            </a>
                            <div class="attachment-preview-body">
                                <div class="attachment-preview-name">{{ $attachment->original_name ?: $attachment->name }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('task-masters.index') }}" class="btn btn-outline-light">Back</a>
            <!-- <a href="{{ route('task-masters.edit', $taskMaster) }}" class="btn btn-app">Edit</a> -->
        </div>
    </main>
@endsection