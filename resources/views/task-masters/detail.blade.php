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

        .detail-accordion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .detail-accordion-toggle {
            border: 1px solid rgba(148, 163, 184, 0.4);
            background: rgba(15, 23, 42, 0.7);
            color: #e2e8f0;
            border-radius: 999px;
            font-size: 0.8rem;
            padding: 0.25rem 0.8rem;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .detail-accordion-toggle:hover,
        .detail-accordion-toggle:focus {
            background: rgba(59, 130, 246, 0.2);
            border-color: rgba(96, 165, 250, 0.7);
            color: #ffffff;
        }

        .detail-grid-accordion {
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: max-height 0.4s ease, opacity 0.3s ease;
        }

        .detail-grid-accordion.is-open {
            opacity: 1;
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

        <div class="detail-accordion-header">
            <h2 class="detail-section-title mb-0">Task Information</h2>
            <button type="button" class="detail-accordion-toggle" data-accordion-toggle data-target="#documentDetailGrid" aria-expanded="true">Hide</button>
        </div>

        <div id="documentDetailGrid" class="detail-grid detail-grid-accordion is-open" data-accordion-panel>
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
                            <div class="detail-accordion-header">
                                <div class="detail-value">Detail #{{ $index + 1 }} - {{ $detail->activity ?: 'No activity name' }}</div>
                                <button type="button" class="detail-accordion-toggle" data-accordion-toggle data-target="#detailGrid{{ $detail->id ?: $index }}" aria-expanded="true">Hide</button>
                            </div>

                            <div id="detailGrid{{ $detail->id ?: $index }}" class="detail-grid detail-grid-accordion is-open" data-accordion-panel>
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const panels = Array.from(document.querySelectorAll('[data-accordion-panel]'));
            const toggles = Array.from(document.querySelectorAll('[data-accordion-toggle]'));

            const openPanel = function (panel) {
                panel.classList.add('is-open');
                panel.style.maxHeight = panel.scrollHeight + 'px';
            };

            const closePanel = function (panel) {
                panel.style.maxHeight = panel.scrollHeight + 'px';
                requestAnimationFrame(function () {
                    panel.style.maxHeight = '0px';
                    panel.classList.remove('is-open');
                });
            };

            const updateToggleLabel = function (button, expanded) {
                button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                button.textContent = expanded ? 'Hide' : 'Show';
            };

            panels.forEach(function (panel) {
                if (panel.classList.contains('is-open')) {
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                }
            });

            toggles.forEach(function (button) {
                const target = button.getAttribute('data-target');
                const panel = target ? document.querySelector(target) : null;

                if (!panel) {
                    return;
                }

                button.addEventListener('click', function () {
                    const isOpen = panel.classList.contains('is-open');

                    if (isOpen) {
                        closePanel(panel);
                        updateToggleLabel(button, false);
                        return;
                    }

                    openPanel(panel);
                    updateToggleLabel(button, true);
                });
            });

            window.addEventListener('resize', function () {
                panels.forEach(function (panel) {
                    if (panel.classList.contains('is-open')) {
                        panel.style.maxHeight = panel.scrollHeight + 'px';
                    }
                });
            });
        });
    </script>
@endpush