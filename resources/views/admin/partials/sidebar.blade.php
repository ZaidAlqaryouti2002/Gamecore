@php
    $sections = [
        'Main menu' => [
            ['Categories', 'tag'],
            ['Products', 'box-seam'],
        ],
        'Management' => [
            ['Users', 'people'],
            ['Orders', 'receipt'],
            ['Reviews', 'star'],
        ],
    ];
@endphp

<aside class="admin-sidebar" id="admin-sidebar" aria-label="Admin sidebar">
    <div class="admin-brand">
        <a href="{{ route('admin.dashboard') }}">
            <span class="admin-brand-icon"><i class="bi bi-controller" aria-hidden="true"></i></span>
            <span>Game<span class="admin-brand-accent">Core</span></span>
        </a>
        <button type="button" class="admin-nav-close" aria-label="Close navigation">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    </div>

    <nav class="admin-nav" aria-label="Admin navigation">
        @foreach ($sections as $label => $items)
            <h2 class="admin-nav-label">{{ $label }}</h2>
            @if ($loop->first)
                <a class="admin-nav-item" href="{{ route('admin.dashboard') }}"
                   @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                    <i class="bi bi-grid" aria-hidden="true"></i>
                    Dashboard
                </a>
            @endif
            @foreach ($items as [$name, $icon])
                @php($route = 'admin.'.strtolower($name).'.index')
                <a class="admin-nav-item" href="{{ route($route) }}"
                   @if (request()->routeIs($route)) aria-current="page" @endif>
                    <i class="bi bi-{{ $icon }}" aria-hidden="true"></i>
                    {{ $name }}
                </a>
            @endforeach
        @endforeach
    </nav>

    <div class="admin-sidebar-bottom">
        <a class="admin-nav-item" href="{{ route('admin.profile') }}"
           @if (request()->routeIs('admin.profile')) aria-current="page" @endif>
            <i class="bi bi-person-circle" aria-hidden="true"></i>
            Profile
        </a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="admin-nav-item admin-logout" type="submit">
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                Logout
            </button>
        </form>
    </div>
</aside>
