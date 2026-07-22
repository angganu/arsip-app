@extends('layouts.app')

@section('title', $mode === 'edit' ? 'Edit Document' : 'Create Document')

@push('styles')
    <style>
        .detail-card {
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 0.85rem;
            background: rgba(2, 6, 23, 0.2);
        }

        #detailRows {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 0.25rem;
        }

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

        .attachment-preview-body {
            padding: 0.75rem;
        }

        .attachment-preview-name {
            font-size: 0.8rem;
            word-break: break-word;
        }

        .attachment-input-hidden {
            display: none;
        }
    </style>
@endpush

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => $mode === 'edit' ? 'Edit Document' : 'Create Document'])

    <main class="app-card p-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <p class="text-light small mb-0">Fill in the form below to save the document.</p>
            </div>
        </div>

        <form method="POST" action="{{ $mode === 'edit' ? route('task-masters.update', $taskMaster) : route('task-masters.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select name="task_category_id" class="form-select" required>
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) old('task_category_id', $taskMaster->task_category_id) === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('task_category_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Task Title <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $taskMaster->name) }}" required>
                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6 col-md-6">
                    <label class="form-label">Planning Start <span class="text-danger">*</span></label>
                    <input type="date" name="date_planning_start" class="form-control" value="{{ old('date_planning_start', optional($taskMaster->date_planning_start)->format('Y-m-d')) }}" required>
                    @error('date_planning_start') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-6 col-md-6">
                    <label class="form-label">Planning Finish <span class="text-danger">*</span></label>
                    <input type="date" name="date_planning_finish" class="form-control" value="{{ old('date_planning_finish', optional($taskMaster->date_planning_finish)->format('Y-m-d')) }}" required>
                    @error('date_planning_finish') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="has_schedule" value="1" id="hasSchedule" {{ old('has_schedule', $taskMaster->has_schedule ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="hasSchedule">Has Schedule</label>
            </div>

            <div id="scheduleFields" class="row g-3 mb-3 {{ old('has_schedule', $taskMaster->has_schedule ?? false) ? '' : 'd-none' }}">
                <div class="col-6 col-md-6">
                    <label class="form-label">Interval Value <span class="text-danger">*</span></label>
                    <input type="number" min="1" name="interval_value" class="form-control" value="{{ old('interval_value', $taskMaster->interval_value ?: 1) }}">
                    @error('interval_value') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-6 col-md-6">
                    <label class="form-label">Interval Schedule <span class="text-danger">*</span></label>
                    <select name="interval_schedule" class="form-select">
                        <option value="">Select interval</option>
                        @foreach ($intervalOptions as $intervalOption)
                            <option value="{{ $intervalOption }}" {{ old('interval_schedule', $selectedInterval) === $intervalOption ? 'selected' : '' }}>{{ ucfirst($intervalOption) }}</option>
                        @endforeach
                    </select>
                    @error('interval_schedule') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $taskMaster->description) }}</textarea>
                @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <hr>
            
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">Task Details</label>
                <button type="button" id="addDetailRow" class="btn btn-sm btn-outline-light">Add</button>
            </div>

            @error('details') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

            <div id="detailRows" class="d-grid gap-3">
                @forelse ($detailRows as $index => $detailRow)
                    <div class="detail-card p-3" data-detail-row>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Detail #<span class="detail-number">{{ $index + 1 }}</span></h6>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-detail>Remove</button>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Activity <span class="text-danger">*</span></label>
                                <input type="text" name="details[{{ $index }}][activity]" class="form-control" value="{{ $detailRow['activity'] ?? '' }}">
                                @error('details.' . $index . '.activity') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-6 col-md-6">
                                <label class="form-label">Planning Start <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="details[{{ $index }}][date_planning_start]" class="form-control" value="{{ $detailRow['date_planning_start'] ?? '' }}">
                                @error('details.' . $index . '.date_planning_start') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-6 col-md-6">
                                <label class="form-label">Planning Finish <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="details[{{ $index }}][date_planning_finish]" class="form-control" value="{{ $detailRow['date_planning_finish'] ?? '' }}">
                                @error('details.' . $index . '.date_planning_finish') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="details[{{ $index }}][description]" class="form-control" rows="2">{{ $detailRow['description'] ?? '' }}</textarea>
                                @error('details.' . $index . '.description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="detail-card p-3" data-detail-row>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Detail #<span class="detail-number">1</span></h6>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-detail>Remove</button>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Activity <span class="text-danger">*</span></label>
                                <input type="text" name="details[0][activity]" class="form-control" value="">
                            </div>

                            <div class="col-6 col-md-6">
                                <label class="form-label">Planning Start <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="details[0][date_planning_start]" class="form-control" value="">
                            </div>

                            <div class="col-6 col-md-6">
                                <label class="form-label">Planning Finish <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="details[0][date_planning_finish]" class="form-control" value="">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="details[0][description]" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <hr>

            <div class="mb-3">
                <label for="attachments" class="form-label">Attachments</label>
                <input type="file" id="attachments" name="attachments[]" class="attachment-input-hidden" accept="image/*" multiple>
                <button type="button" id="selectAttachmentsButton" class="btn btn-outline-light">Select Images</button>
                <div class="form-text text-light">Upload one or more images. You can remove selected images before saving.</div>
                @error('attachments') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @error('attachments.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div id="attachmentPreviewWrapper" class="mb-3 d-none">
                <label class="form-label">Preview</label>
                <div id="attachmentPreviewPanel" class="attachment-preview-grid"></div>
            </div>

            <div class="mt-4">
                <a href="{{ route('task-masters.index') }}" class="btn btn-outline-light">Back</a>
                <button type="submit" class="btn btn-app">Save</button>
            </div>
        </form>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const hasScheduleInput = document.getElementById('hasSchedule');
            const scheduleFields = document.getElementById('scheduleFields');
            const detailRowsContainer = document.getElementById('detailRows');
            const addDetailRowButton = document.getElementById('addDetailRow');
            const attachmentsInput = document.getElementById('attachments');
            const selectAttachmentsButton = document.getElementById('selectAttachmentsButton');
            const attachmentPreviewWrapper = document.getElementById('attachmentPreviewWrapper');
            const attachmentPreviewPanel = document.getElementById('attachmentPreviewPanel');
            let selectedAttachmentFiles = [];

            if (!hasScheduleInput || !scheduleFields) {
                return;
            }

            const toggleScheduleFields = function () {
                scheduleFields.classList.toggle('d-none', !hasScheduleInput.checked);
            };

            hasScheduleInput.addEventListener('change', toggleScheduleFields);
            toggleScheduleFields();

            const createDetailRow = function (index) {
                const wrapper = document.createElement('div');
                wrapper.className = 'detail-card p-3';
                wrapper.setAttribute('data-detail-row', '');
                wrapper.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Detail #<span class="detail-number">${index + 1}</span></h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-detail>Remove</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Activity <span class="text-danger">*</span></label>
                            <input type="text" name="details[${index}][activity]" class="form-control">
                        </div>
                        <div class="col-6 col-md-6">
                            <label class="form-label">Planning Start <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="details[${index}][date_planning_start]" class="form-control">
                        </div>
                        <div class="col-6 col-md-6">
                            <label class="form-label">Planning Finish <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="details[${index}][date_planning_finish]" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="details[${index}][description]" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                `;

                return wrapper;
            };

            const renumberRows = function () {
                const rows = detailRowsContainer.querySelectorAll('[data-detail-row]');

                rows.forEach(function (row, index) {
                    const number = row.querySelector('.detail-number');
                    if (number) {
                        number.textContent = String(index + 1);
                    }

                    const controls = row.querySelectorAll('input, textarea, select');
                    controls.forEach(function (control) {
                        const currentName = control.getAttribute('name');
                        if (!currentName) {
                            return;
                        }

                        control.setAttribute('name', currentName.replace(/details\[\d+\]/, `details[${index}]`));
                    });
                });
            };

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
                if (!attachmentsInput || !attachmentPreviewWrapper || !attachmentPreviewPanel) {
                    return;
                }

                attachmentPreviewPanel.innerHTML = '';
                attachmentPreviewWrapper.classList.toggle('d-none', selectedAttachmentFiles.length === 0);

                selectedAttachmentFiles.forEach(function (file, index) {
                    const reader = new FileReader();
                    reader.addEventListener('load', function (event) {
                        const card = document.createElement('div');
                        card.className = 'attachment-preview-card';
                        card.innerHTML = `
                            <img src="${event.target?.result || ''}" alt="${file.name}" class="attachment-preview-image">
                            <div class="attachment-preview-body">
                                <div class="attachment-preview-name text-light mb-2">${file.name}</div>
                                <button type="button" class="btn btn-sm btn-outline-danger w-100" data-remove-attachment="${index}">Delete</button>
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

            if (detailRowsContainer && addDetailRowButton) {
                addDetailRowButton.addEventListener('click', function () {
                    const nextIndex = detailRowsContainer.querySelectorAll('[data-detail-row]').length;
                    detailRowsContainer.appendChild(createDetailRow(nextIndex));
                    renumberRows();
                });

                detailRowsContainer.addEventListener('click', function (event) {
                    const removeButton = event.target.closest('[data-remove-detail]');
                    if (!removeButton) {
                        return;
                    }

                    const rows = detailRowsContainer.querySelectorAll('[data-detail-row]');
                    const row = removeButton.closest('[data-detail-row]');
                    if (!row || rows.length <= 1) {
                        return;
                    }

                    row.remove();
                    renumberRows();
                });

                renumberRows();
            }

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