@extends('layouts.app')

@section('title', 'Task Discussion')

@push('styles')
    <style>
        .discussion-wrap {
            display: grid;
            gap: 1rem;
        }

        .discussion-head {
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(15, 23, 42, 0.45);
            border-radius: 0.9rem;
            padding: 1rem;
        }

        .discussion-title {
            color: #f8fafc;
            font-size: 1rem;
            margin-bottom: 0.3rem;
            font-weight: 600;
        }

        .discussion-meta {
            color: #cbd5e1;
            font-size: 0.85rem;
        }

        .discussion-list {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.9rem;
            background: rgba(2, 6, 23, 0.2);
            padding: 1rem;
            display: grid;
            gap: 0.85rem;
            max-height: 60vh;
            overflow-y: auto;
        }

        .discussion-message {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.8rem;
            padding: 0.75rem;
            background: rgba(15, 23, 42, 0.55);
        }

        .discussion-message--administrator {
            border-color: rgba(59, 130, 246, 0.55);
            background: rgba(30, 64, 175, 0.2);
        }

        .discussion-message--manager {
            border-color: rgba(16, 185, 129, 0.5);
            background: rgba(6, 95, 70, 0.24);
        }

        .discussion-message__header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 0.75rem;
            margin-bottom: 0.4rem;
        }

        .discussion-message__author {
            color: #f8fafc;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .discussion-message__role {
            color: #93c5fd;
            font-size: 0.75rem;
            margin-left: 0.35rem;
            text-transform: capitalize;
        }

        .discussion-message__role--administrator {
            color: #93c5fd;
        }

        .discussion-message__role--manager {
            color: #6ee7b7;
        }

        .discussion-message__time {
            color: #94a3b8;
            font-size: 0.75rem;
            white-space: nowrap;
        }

        .discussion-message__body {
            color: #e2e8f0;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .discussion-form {
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(15, 23, 42, 0.45);
            border-radius: 0.9rem;
            padding: 1rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const discussionList = document.querySelector('.discussion-list');

            if (discussionList) {
                discussionList.scrollTop = discussionList.scrollHeight;
            }
        });
    </script>
@endpush

@section('content')
    @include('partials.dashboard-nav', ['dashboardRoute' => route('admin.dashboard'), 'pageTitle' => 'Task Discussion'])

    <main class="app-card p-3 p-md-4 flex-grow-1">
        <div class="discussion-wrap">
            <div class="discussion-head">
                <div class="discussion-title">{{ $taskMaster->name }}</div>
                <div class="discussion-meta">{{ $taskMaster->code }} · {{ $taskMaster->category?->name ?: 'No category' }}</div>
            </div>

            @if (session('success'))
                <div class="alert alert-success py-2 px-3 mb-0">{{ session('success') }}</div>
            @endif

            <div class="discussion-list">
                @forelse ($taskMaster->discussions as $discussion)
                    @php
                        $senderRoles = $discussion->user?->roles?->pluck('name') ?? collect();
                        $senderType = 'user';

                        if ((int) $discussion->base_user_id === (int) $taskMaster->planned_by || $senderRoles->contains('administrator')) {
                            $senderType = 'administrator';
                        } elseif ($senderRoles->contains('manager')) {
                            $senderType = 'manager';
                        }
                    @endphp
                    <article class="discussion-message discussion-message--{{ $senderType }}">
                        <div class="discussion-message__header">
                            <div class="discussion-message__author">
                                {{ $discussion->user?->name ?: 'Unknown User' }}
                                <!-- <span class="discussion-message__role discussion-message__role--{{ $senderType }}">{{ $senderType }}</span> -->
                            </div>
                            <div class="discussion-message__time">{{ optional($discussion->created_at)->format('Y-m-d H:i') }}</div>
                        </div>
                        <div class="discussion-message__body">{{ $discussion->message }}</div>
                    </article>
                @empty
                    <div class="text-center text-light-emphasis py-4">No discussion yet.</div>
                @endforelse
            </div>

            <form method="POST" action="{{ route('task-masters.discussion.store', $taskMaster) }}" class="discussion-form">
                @csrf
                <label for="message" class="form-label text-light">Message</label>
                <textarea name="message" id="message" rows="4" class="form-control" maxlength="5000" placeholder="Type your discussion message..." required>{{ old('message') }}</textarea>
                @error('message') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('task-masters.index') }}" class="btn btn-outline-light">Back</a>
                    <button type="submit" class="btn btn-app">Send</button>
                </div>
            </form>
        </div>
    </main>
@endsection
