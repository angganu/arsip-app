@props([
    'dashboardRoute' => '#',
    'pageTitle' => null,
])

@php
    $avatarPath = auth()->user()->profile?->avatar_path;
    $avatarUrl = $avatarPath
        ? asset('storage/' . $avatarPath)
        : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=1f6feb&color=ffffff&size=64';
    $isManager = auth()->user()->roles()->where('name', 'manager')->exists();
    $dashboardRoute = $isManager
        ? route('manager.dashboard')
        : route('admin.dashboard');
    $pageTitle = $pageTitle ?: trim($__env->yieldContent('title'));
    $pageTitle = $pageTitle ?: 'Arsip App';

    $isDashboardActive = request()->routeIs(['admin.dashboard', 'manager.dashboard']);
    $isDocumentMenuActive = request()->routeIs(['task-categories.*', 'task-masters.*']);
    $isCreateDocumentActive = request()->routeIs('task-masters.create');
    $isListDocumentActive = request()->routeIs(['task-masters.index', 'task-masters.show', 'task-masters.edit', 'task-masters.discussion']);
    $isTaskCategoryActive = request()->routeIs('task-categories.*');
    $isDepartmentActive = request()->routeIs('departments.*');
    $isUserMenuActive = request()->routeIs('base-users.*');
@endphp

<nav class="navbar app-card mb-3 px-2 py-2 navbar-dark">
    <div class="container-fluid p-0">
        <button
            class="navbar-toggler border-0"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#sidebarMenu"
            aria-controls="sidebarMenu"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <span class="navbar-brand mb-0 h1 fs-6">{{ $pageTitle }}</span>

        <div class="dropdown ms-auto">
            <button
                class="btn btn-link text-decoration-none text-white p-0 d-flex align-items-center gap-2"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >
                <!-- <span class="small fw-semibold d-none d-sm-inline">{{ auth()->user()->name }}</span> -->
                <img
                    src="{{ $avatarUrl }}"
                    alt="Profile Photo"
                    width="34"
                    height="34"
                    class="rounded-circle border border-light border-opacity-50"
                >
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                <li><a class="dropdown-item" href="{{ route('password.edit') }}">Change Password</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <div
        class="offcanvas offcanvas-start text-bg-dark border-end border-secondary"
        tabindex="-1"
        id="sidebarMenu"
        aria-labelledby="sidebarMenuLabel"
    >
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="sidebarMenuLabel">Menu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="nav nav-pills flex-column gap-1">
                <!-- <li class="nav-item">
                    <button
                        class="nav-link text-start w-100 d-flex justify-content-between align-items-center {{ $isDocumentMenuActive ? 'active' : '' }}"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#documentControllSubmenu"
                        aria-expanded="{{ $isDocumentMenuActive ? 'true' : 'false' }}"
                        aria-controls="documentControllSubmenu"
                    >
                        <span>Document Controll</span>
                        <span class="small">{{ $isDocumentMenuActive ? '−' : '+' }}</span>
                    </button>
                    <div class="collapse mt-1 {{ $isDocumentMenuActive ? 'show' : '' }}" id="documentControllSubmenu">
                        <ul class="nav nav-pills flex-column ms-3 gap-1">
                            <li class="nav-item">
                                <a class="nav-link {{ $isCreateDocumentActive ? 'active' : '' }}" href="{{ route('task-masters.create') }}">Create New Task</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $isListDocumentActive ? 'active' : '' }}" href="{{ route('task-masters.index') }}">Task List</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $isTaskCategoryActive ? 'active' : '' }}" href="{{ route('task-categories.index') }}">Document Categories</a>
                            </li>
                        </ul>
                    </div>
                </li> -->

                <li class="nav-item">
                    <a class="nav-link {{ $isDashboardActive ? 'active' : '' }}" href="{{ $dashboardRoute }}">Dashboard</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ $isListDocumentActive ? 'active' : '' }}" href="{{ route('task-masters.index') }}">Task List</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ $isTaskCategoryActive ? 'active' : '' }}" href="{{ route('task-categories.index') }}">Task Categories</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">Report</a>
                </li>

                @if ($isManager)
                    <li class="nav-item">
                        <hr class="border-secondary my-1">
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ $isDepartmentActive ? 'active' : '' }}" href="{{ route('departments.index') }}">Department</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ $isUserMenuActive ? 'active' : '' }}" href="{{ route('base-users.index') }}">Users</a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
