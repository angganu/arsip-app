@extends('layouts.app')

@section('title', 'Login - Arsip App')

@section('content')
    <main class="d-flex flex-column justify-content-center flex-grow-1">
        <section class="app-card p-4 p-sm-4">
            <p class="text-uppercase small mb-1 text-info fw-semibold">Arsip App</p>
            <h1 class="h3 mb-2">Masuk ke Akun Anda</h1>
            <p class="text-light-emphasis mb-4">Akses dashboard sesuai peran manager atau administrator.</p>

            @if ($errors->any())
                <div class="alert alert-danger py-2" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.attempt') }}" method="POST" class="d-grid gap-3">
                @csrf

                <div>
                    <label for="email" class="form-label">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="form-control form-control-lg"
                        placeholder="you@example.com"
                    >
                </div>

                <div>
                    <label for="password" class="form-label">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        class="form-control form-control-lg"
                        placeholder="Masukkan password"
                    >
                </div>

                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember" value="1">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>

                <button type="submit" class="btn btn-app btn-lg w-100">Login</button>
            </form>
        </section>

        <section class="app-card p-3 mt-3">
            <p class="mb-1 fw-semibold">Sample akun seeder</p>
            <p class="mb-1 small text-light-emphasis">manager@example.com / password123</p>
            <p class="mb-0 small text-light-emphasis">admin@example.com / password123</p>
        </section>
    </main>
@endsection
