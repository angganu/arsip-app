@extends('layouts.app')

@section('title', 'Manager Dashboard')

@section('content')
    <header class="app-card p-4">
        <p class="text-uppercase small text-success mb-1">Manager Area</p>
        <h1 class="h3 mb-2">Manager Dashboard</h1>
        <p class="mb-0 text-light-emphasis">Selamat datang, {{ auth()->user()->name }}.</p>
    </header>

    <main class="app-card p-4 mt-3 flex-grow-1">
        <p class="mb-2">Halaman ini hanya bisa diakses oleh user dengan role manager.</p>
        <p class="mb-0 text-light-emphasis small">Gunakan area ini untuk fitur monitoring, approval, dan operasional manajerial.</p>
    </main>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="btn btn-danger w-100 btn-lg">Logout</button>
    </form>
@endsection
