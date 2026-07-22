@extends('layouts.app')

@section('title', 'Administrator Dashboard')

@section('content')
    <header class="app-card p-4">
        <p class="text-uppercase small text-warning mb-1">Administrator Area</p>
        <h1 class="h3 mb-2">Administrator Dashboard</h1>
        <p class="mb-0 text-light-emphasis">Selamat datang, {{ auth()->user()->name }}.</p>
    </header>

    <main class="app-card p-4 mt-3 flex-grow-1">
        <p class="mb-2">Halaman ini hanya bisa diakses oleh user dengan role administrator.</p>
        <p class="mb-0 text-light-emphasis small">Gunakan area ini untuk pengaturan sistem, pengelolaan user, dan audit data.</p>
    </main>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="btn btn-danger w-100 btn-lg">Logout</button>
    </form>
@endsection
