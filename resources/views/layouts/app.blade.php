<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Arsip App')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <style>
        :root {
            --app-primary: #1f6feb;
            --app-bg: #0f172a;
            --app-surface: rgba(255, 255, 255, 0.1);
            --app-border: rgba(255, 255, 255, 0.2);
        }

        body {
            min-height: 100svh;
            background: radial-gradient(circle at 20% 10%, #1e3a8a 0%, rgba(30, 58, 138, 0) 40%),
                radial-gradient(circle at 90% 90%, #0ea5e9 0%, rgba(14, 165, 233, 0) 35%),
                var(--app-bg);
            color: #f8fafc;
        }

        .app-shell {
            width: 100%;
            max-width: 480px;
            min-height: calc(100svh - 2rem);
            margin: 1rem auto;
            border: 1px solid var(--app-border);
            border-radius: 1.25rem;
            background: var(--app-surface);
            backdrop-filter: blur(8px);
            box-shadow: 0 24px 48px rgba(2, 6, 23, 0.45);
        }

        .app-card {
            border: 1px solid var(--app-border);
            border-radius: 1rem;
            background: rgba(2, 6, 23, 0.35);
        }

        .btn-app {
            background-color: var(--app-primary);
            border-color: var(--app-primary);
            color: #fff;
            font-weight: 600;
        }

        .btn-app:hover,
        .btn-app:focus {
            background-color: #3b82f6;
            border-color: #3b82f6;
            color: #fff;
        }

        @media (max-width: 576px) {
            .app-shell {
                min-height: 100svh;
                margin: 0;
                border-radius: 0;
                border-left: 0;
                border-right: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="app-shell d-flex flex-column p-3 p-sm-4">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
