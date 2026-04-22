<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Kalender Event Perusahaan — Jadwal, Acara, dan Pengingat Korporat">
    <title>@yield('title', 'Kalender Event') — KalendarPro</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('head')
</head>
<body>

@auth
<div class="app-shell app-shell-active">
    <!-- ─── APP SIDEBAR ────────────────────────────────────────────────────────── -->
    <aside class="app-sidebar" id="appSidebar">
        <div class="app-sidebar-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; gap: 0.5rem;">
            <div class="app-sidebar-logo" style="display: flex; align-items: center; gap: 0.8rem; flex: 1; overflow: hidden; padding: 0;">
                <img src="{{ asset('images/logobaru.png') }}" alt="Logo" style="height: 32px; flex-shrink: 0;">
                <span class="brand-text" style="font-weight: 800; font-size: 0.9rem; color: var(--cal-accent); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ config('app.name', 'Kalender') }}
                </span>
            </div>
            <button id="sidebarToggle" style="background: var(--bg-surface); border: 1px solid var(--border-color); cursor: pointer; color: var(--text-primary); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
        </div>

        <nav class="app-sidebar-nav">
            <a href="{{ route('home') }}" class="app-sidebar-link {{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Kalender</span>
            </a>
            <a href="{{ route('dashboard') }}" class="app-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
            @if(Auth::user()->isAdmin())
            <a href="#" class="app-sidebar-link" id="manageUsersBtnSidebar">
                <i class="fa-solid fa-users-gear"></i>
                <span>Kelola Akun</span>
            </a>
            @endif
        </nav>

        <div class="app-sidebar-footer">
            <div class="app-sidebar-user">
                <div class="app-sidebar-user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="app-sidebar-user-info">
                    <span class="app-sidebar-user-name">{{ Auth::user()->name }}</span>
                    <span class="app-sidebar-user-role role-{{ Auth::user()->role }}">
                        {{ Auth::user()->department ? Auth::user()->department->name : ucfirst(Auth::user()->role) }}
                    </span>
                </div>
            </div>

            <div class="theme-toggle-container">
                <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode" style="width: 100%; border-radius: 12px;">
                    <i class="fa-solid fa-sun" id="themeIconLight"></i>
                    <i class="fa-solid fa-moon" id="themeIconDark"></i>
                    <span style="margin-left: 0.5rem; font-size: 0.85rem; font-weight: 600;" class="theme-label">Tema</span>
                </button>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm btn-logout-sidebar" style="width: 100%; justify-content: center;">
                    <i class="fa-solid fa-right-from-bracket"></i> <span class="logout-label">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="app-main">
@else
<div class="app-shell app-shell-none">
    <div class="app-main">
        <!-- ─── NAVBAR (Guests Only) ──────────────────────────────────────────────── -->
        <nav class="navbar" id="navbar">
            <div class="navbar-container">
                <a href="{{ route('home') }}" class="navbar-brand">
                    <img src="{{ asset('images/logobaru.png') }}" alt="Logo" class="brand-logo-img">
                </a>

                <div class="navbar-right">
                    <button class="theme-toggle" id="themeToggleGuest" title="Toggle Dark Mode">
                        <i class="fa-solid fa-sun" id="themeIconLightGuest"></i>
                        <i class="fa-solid fa-moon" id="themeIconDarkGuest"></i>
                    </button>
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-lock"></i> Login
                    </a>
                </div>
            </div>
        </nav>
@endauth

    <!-- ─── FLASH MESSAGES ──────────────────────────────────────────────────────── -->
    @if(session('success'))
    <div class="flash flash-success" id="flashMsg">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" class="flash-close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    @endif

    <!-- ─── MAIN CONTENT ────────────────────────────────────────────────────────── -->
    <main class="main-content">
        @yield('content')
    </main>
</div>
</div>

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<!-- App JS -->
<script>
// ─── Dark Mode ─────────────────────────────────────────────────────────────────
(function() {
    const html = document.documentElement;
    const saved = localStorage.getItem('theme') || 'light';

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        
        // Update all toggle icons (Sidebar & Guest Navbar)
        const iconsLight = document.querySelectorAll('#themeIconLight, #themeIconLightGuest');
        const iconsDark = document.querySelectorAll('#themeIconDark, #themeIconDarkGuest');
        
        if (theme === 'dark') {
            iconsLight.forEach(i => i.style.display = 'none');
            iconsDark.forEach(i => i.style.display = 'inline');
        } else {
            iconsLight.forEach(i => i.style.display = 'inline');
            iconsDark.forEach(i => i.style.display = 'none');
        }
    }

    applyTheme(saved);

    // Listen to all possible toggle buttons
    document.querySelectorAll('#themeToggle, #themeToggleGuest').forEach(btn => {
        btn.addEventListener('click', () => {
            const current = html.getAttribute('data-theme');
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
    });
})();

// ─── User Menu Dropdown ────────────────────────────────────────────────────────
const userMenuBtn = document.getElementById('userMenuBtn');
const userDropdown = document.getElementById('userDropdown');
if (userMenuBtn) {
    userMenuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown.classList.toggle('open');
    });
    document.addEventListener('click', () => userDropdown.classList.remove('open'));
}

// ─── Flash auto hide ──────────────────────────────────────────────────────────
const flash = document.getElementById('flashMsg');
if (flash) {
    setTimeout(() => flash.style.opacity = '0', 4000);
    setTimeout(() => flash.remove(), 4500);
}

// ─── Sidebar Toggle ──────────────────────────────────────────────────────────
(function() {
    const shell = document.querySelector('.app-shell');
    const toggleBtn = document.getElementById('sidebarToggle');
    if (!shell || !toggleBtn) return;

    // Load state
    const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    if (isCollapsed) shell.classList.add('collapsed');

    toggleBtn.addEventListener('click', () => {
        shell.classList.toggle('collapsed');
        localStorage.setItem('sidebar-collapsed', shell.classList.contains('collapsed'));
        
        // Trigger resize for FullCalendar
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 300);
    });
})();

// ─── Navbar scroll shadow ─────────────────────────────────────────────────────
window.addEventListener('scroll', () => {
    const nav = document.getElementById('navbar');
    if (window.scrollY > 10) nav.classList.add('scrolled');
    else nav.classList.remove('scrolled');
});
</script>

@stack('scripts')
</body>
</html>
