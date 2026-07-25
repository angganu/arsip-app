<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $taskMaster->name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            line-height: 1.4;
        }

        h1, h2 {
            margin: 0;
            color: #0f172a;
        }

        .header {
            margin-bottom: 16px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 10px;
        }

        .subtitle {
            color: #475569;
            margin-top: 4px;
        }

        .section {
            margin-top: 16px;
        }

        .section h2 {
            font-size: 14px;
            margin-bottom: 8px;
        }

        .meta-table,
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            vertical-align: top;
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
        }

        .meta-label {
            width: 160px;
            font-weight: 700;
            background: #f8fafc;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #e2e8f0;
            padding: 6px;
            vertical-align: top;
        }

        .data-table th {
            background: #f8fafc;
            text-align: left;
        }

        .small {
            color: #64748b;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('texts.archive_document_title') }}</h1>
        <div class="subtitle">{{ __('texts.generated_at') }}: {{ $generatedAt->format('Y-m-d H:i:s') }}</div>
    </div>

    <div class="section">
        <h2>{{ __('texts.task_information') }}</h2>
        <table class="meta-table">
            <tr>
                <td class="meta-label">{{ __('texts.code') }}</td>
                <td>{{ $taskMaster->code ?: __('texts.none') }}</td>
            </tr>
            <tr>
                <td class="meta-label">{{ __('texts.task_title') }}</td>
                <td>{{ $taskMaster->name ?: __('texts.none') }}</td>
            </tr>
            <tr>
                <td class="meta-label">{{ __('texts.category') }}</td>
                <td>{{ $taskMaster->category?->name ?: __('texts.no_category') }}</td>
            </tr>
            <tr>
                <td class="meta-label">{{ __('texts.planned_by') }}</td>
                <td>{{ $taskMaster->planner?->name ?: __('texts.none') }}</td>
            </tr>
            <tr>
                <td class="meta-label">{{ __('texts.planning_date') }}</td>
                <td>
                    {{ optional($taskMaster->date_planning_start)->format('Y-m-d') ?: __('texts.none') }}
                    -
                    {{ optional($taskMaster->date_planning_finish)->format('Y-m-d') ?: __('texts.none') }}
                    ({{ $taskMaster->duration_planning ?? 0 }} {{ __('texts.day_suffix') }})
                </td>
            </tr>
            <tr>
                <td class="meta-label">{{ __('texts.scheduled') }}</td>
                <td>{{ $taskMaster->has_schedule ? __('texts.every').' '.$taskMaster->interval_value.' '.$intervalLabel : __('texts.no_schedule') }}</td>
            </tr>
            <tr>
                <td class="meta-label">{{ __('texts.description') }}</td>
                <td>{{ $taskMaster->description ?: __('texts.none') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>{{ __('texts.task_details') }} ({{ $taskMaster->details->count() }})</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('texts.activity_code') }}</th>
                    <th>{{ __('texts.activity_name') }}</th>
                    <th>{{ __('texts.status') }}</th>
                    <th>{{ __('texts.date_planning') }}</th>
                    <th>{{ __('texts.date_realization') }}</th>
                    <th>{{ __('texts.description') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($taskMaster->details as $detail)
                    @php
                        $statusLabel = match ((int) ($detail->status ?? 0)) {
                            1 => __('texts.on_progress'),
                            2 => __('texts.done'),
                            3 => __('texts.hold'),
                            default => __('texts.new_task'),
                        };
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $detail->code ?: __('texts.none') }}</td>
                        <td>{{ $detail->activity ?: __('texts.none') }}</td>
                        <td>{{ $statusLabel }}</td>
                        <td>
                            {{ optional($detail->date_planning_start)->format('Y-m-d H:i') ?: __('texts.none') }}
                            <br>
                            <span class="small">{{ optional($detail->date_planning_finish)->format('Y-m-d H:i') ?: __('texts.none') }}</span>
                        </td>
                        <td>
                            {{ optional($detail->date_realization_start)->format('Y-m-d H:i') ?: __('texts.none') }}
                            <br>
                            <span class="small">{{ optional($detail->date_realization_finish)->format('Y-m-d H:i') ?: __('texts.none') }}</span>
                        </td>
                        <td>{{ $detail->description ?: __('texts.none') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">{{ __('texts.no_task_details') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>{{ __('texts.task_attachments') }} ({{ $taskMaster->attachments->count() }})</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('texts.name') }}</th>
                    <th>{{ __('texts.description') }}</th>
                    <th>{{ __('texts.file_size') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($taskMaster->attachments as $attachment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $attachment->original_name ?: $attachment->name }}</td>
                        <td>{{ $attachment->description ?: __('texts.none') }}</td>
                        <td>{{ (int) ($attachment->size ?? 0) }} KB</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">{{ __('texts.no_attachments') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
