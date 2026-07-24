@extends('layouts.app')

@section('title', __('texts.submit_realization'))

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => __('texts.submit_realization')])

    <main class="app-card p-4 flex-grow-1" data-delete-label="{{ __('texts.delete') }}">
        <div class="mb-3">
            <div class="text-light-emphasis small">{{ $taskMaster->code }}</div>
            <div class="text-light"><strong>{{ __('texts.task_label') }}:</strong> {{ $taskMaster->name }}</div>
            <div class="text-light"><strong>{{ __('texts.activity') }}:</strong> {{ $taskDetail->activity }}</div>
        </div>

        <form method="POST" action="{{ route('task-masters.details.realization.update', [$taskMaster, $taskDetail]) }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">{{ __('texts.realization_start') }} <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="date_realization_start" class="form-control" value="{{ old('date_realization_start', optional($taskDetail->date_realization_start)->format('Y-m-d\TH:i')) }}" required>
                    @error('date_realization_start') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">{{ __('texts.realization_finish') }} <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="date_realization_finish" class="form-control" value="{{ old('date_realization_finish', optional($taskDetail->date_realization_finish)->format('Y-m-d\TH:i')) }}" required>
                    @error('date_realization_finish') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('texts.note') }}</label>
                <textarea name="note" class="form-control" rows="4">{{ old('note', $taskDetail->note) }}</textarea>
                @error('note') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('texts.attachments') }}</label>
                <input type="file" id="attachments" name="attachments[]" class="d-none" accept="image/*" multiple>
                <button type="button" id="selectAttachmentsButton" class="btn btn-outline-light">{{ __('texts.select_images') }}</button>
                <div class="form-text text-light-emphasis">You can upload one or more files.</div>
                @error('attachments') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @error('attachments.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div id="attachmentPreviewWrapper" class="mb-3 d-none">
                <label class="form-label">{{ __('texts.preview_selected_images') }} (<span id="attachmentsCount">0</span>)</label>
                <div id="attachmentPreviewPanel" class="row g-3"></div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <a href="{{ route('task-masters.show', $taskMaster) }}" class="btn btn-outline-light">{{ __('texts.back') }}</a>
                <button type="submit" class="btn btn-app">{{ __('texts.save') }}</button>
            </div>
        </form>
    </main>
@endsection

@push('styles')
    <style>
        .attachment-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 1rem;
        }

        .attachment-preview-card {
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 0.85rem;
            background: rgba(2, 6, 23, 0.2);
            overflow: hidden;
        }

        .attachment-preview-image {
            width: 100%;
            height: 130px;
            object-fit: cover;
            display: block;
            background: rgba(15, 23, 42, 0.6);
        }

        .attachment-preview-link {
            display: block;
            text-decoration: none;
        }

        .attachment-preview-body {
            padding: 0.75rem;
        }

        .attachment-preview-name {
            font-size: 0.8rem;
            word-break: break-word;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pageCard = document.querySelector('main.app-card');
            const deleteLabel = pageCard?.dataset.deleteLabel || 'Delete';
            const attachmentsInput = document.getElementById('attachments');
            const selectAttachmentsButton = document.getElementById('selectAttachmentsButton');
            const attachmentPreviewWrapper = document.getElementById('attachmentPreviewWrapper');
            const attachmentPreviewPanel = document.getElementById('attachmentPreviewPanel');
            const attachmentsCount = document.getElementById('attachmentsCount');
            let selectedAttachmentFiles = [];

            const getFileSignature = function (file) {
                return [file.name, file.size, file.lastModified, file.type].join(':');
            };

            const syncAttachmentInput = function () {
                if (!attachmentsInput) {
                    return;
                }

                const dataTransfer = new DataTransfer();
                selectedAttachmentFiles.forEach(function (file) {
                    dataTransfer.items.add(file);
                });

                attachmentsInput.files = dataTransfer.files;
            };

            const renderAttachmentPreviews = function () {
                if (!attachmentPreviewWrapper || !attachmentPreviewPanel) {
                    return;
                }

                attachmentPreviewPanel.innerHTML = '';
                attachmentPreviewWrapper.classList.toggle('d-none', selectedAttachmentFiles.length === 0);

                if (attachmentsCount) {
                    attachmentsCount.textContent = String(selectedAttachmentFiles.length);
                }

                selectedAttachmentFiles.forEach(function (file, index) {
                    const reader = new FileReader();
                    reader.addEventListener('load', function (event) {
                        const previewUrl = event.target?.result || '';
                        const card = document.createElement('div');
                        card.className = 'col-12 col-sm-6 col-md-4';
                        card.innerHTML = `
                            <div class="attachment-preview-card">
                                <a href="${previewUrl}" target="_blank" rel="noopener noreferrer" class="attachment-preview-link">
                                    <img src="${previewUrl}" alt="${file.name}" class="attachment-preview-image">
                                </a>
                                <div class="attachment-preview-body">
                                    <div class="attachment-preview-name text-light mb-2">${file.name}</div>
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100" data-remove-attachment="${index}">${deleteLabel}</button>
                                </div>
                            </div>
                        `;

                        attachmentPreviewPanel.appendChild(card);
                    });

                    reader.readAsDataURL(file);
                });
            };

            const appendAttachmentFiles = function (files) {
                const existingSignatures = new Set(selectedAttachmentFiles.map(getFileSignature));

                files.forEach(function (file) {
                    const signature = getFileSignature(file);

                    if (!existingSignatures.has(signature)) {
                        selectedAttachmentFiles.push(file);
                        existingSignatures.add(signature);
                    }
                });

                syncAttachmentInput();
                renderAttachmentPreviews();
            };

            if (attachmentsInput && attachmentPreviewPanel) {
                if (selectAttachmentsButton) {
                    selectAttachmentsButton.addEventListener('click', function () {
                        attachmentsInput.click();
                    });
                }

                attachmentsInput.addEventListener('change', function (event) {
                    const files = Array.from(event.target.files || []);

                    if (files.length === 0) {
                        return;
                    }

                    appendAttachmentFiles(files);
                });

                attachmentPreviewPanel.addEventListener('click', function (event) {
                    const removeButton = event.target.closest('[data-remove-attachment]');
                    if (!removeButton) {
                        return;
                    }

                    const removeIndex = Number(removeButton.getAttribute('data-remove-attachment'));
                    selectedAttachmentFiles = selectedAttachmentFiles.filter(function (_file, index) {
                        return index !== removeIndex;
                    });

                    syncAttachmentInput();
                    renderAttachmentPreviews();
                });

                selectedAttachmentFiles = Array.from(attachmentsInput.files || []);
                renderAttachmentPreviews();
            }
        });
    </script>
@endpush
