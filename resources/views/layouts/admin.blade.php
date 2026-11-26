<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | GameCore Admin</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('gamecore-favicon.svg') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600;700;800&amp;display=swap">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
    <script src="{{ asset('js/admin-navigation.js') }}" defer></script>
</head>
<body>
    <a class="admin-skip-link" href="#admin-content">Skip to content</a>
    @include('admin.partials.sidebar')
    <div class="admin-backdrop" data-admin-backdrop hidden></div>

    <div class="admin-main">
        <header class="admin-topbar">
            <button type="button" class="admin-nav-toggle" aria-label="Open navigation"
                    aria-expanded="false" aria-controls="admin-sidebar">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>
            <div class="admin-topbar-heading">
                <h1>@yield('title', 'Dashboard')</h1>
                @yield('topbar-description')
            </div>
            @yield('topbar-end')
        </header>

        <main class="admin-content" id="admin-content" tabindex="-1">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
