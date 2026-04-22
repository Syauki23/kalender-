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

<!-- ─── NAVBAR ─────────────────────────────────────────────────────────────── -->
<nav class="navbar" id="navbar">
    <div class="navbar-container">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="navbar-brand">
            <img src="{{ asset('images/logobaru.png') }}" alt="Logo" class="brand-logo-img">
        </a>

        <!-- Nav Links -->
        <div class="navbar-links">
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar"></i> Kalender
            </a>
            @auth
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
            @endauth
        </div>

        <!-- Right Side -->
        <div class="navbar-right">
            <!-- Dark Mode Toggle -->
            <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode" aria-label="Toggle dark mode">
                <i class="fa-solid fa-sun" id="themeIconLight"></i>
                <i class="fa-solid fa-moon" id="themeIconDark"></i>
            </button>

            @auth
            <!-- User Dropdown -->
            <div class="user-menu" id="userMenu">
                <button class="user-btn" id="userMenuBtn">
                    <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    <span class="user-name">{{ Auth::user()->name }}</span>
                    <span class="user-role-badge role-{{ Auth::user()->role }}">{{ ucfirst(Auth::user()->role) }}</span>
                    <i class="fa-solid fa-chevron-down user-chevron"></i>
                </button>
                <div class="user-dropdown" id="userDropdown">
                    <div class="dropdown-header">
                        <span class="dropdown-name">{{ Auth::user()->name }}</span>
                        <span class="dropdown-email">{{ Auth::user()->email }}</span>
                    </div>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item dropdown-item-danger">
                            <i class="fa-solid fa-right-from-bracket"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-lock"></i> Login
            </a>
            @endauth
        </div>
    </div>
</nav>

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

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<!-- App JS -->
<script>
// ─── Dark Mode ─────────────────────────────────────────────────────────────────
(function() {
    const html = document.documentElement;
    const btn = document.getElementById('themeToggle');
    const iconLight = document.getElementById('themeIconLight');
    const iconDark = document.getElementById('themeIconDark');
    const saved = localStorage.getItem('theme') || 'light';

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        if (theme === 'dark') {
            iconLight.style.display = 'none';
            iconDark.style.display = 'inline';
        } else {
            iconLight.style.display = 'inline';
            iconDark.style.display = 'none';
        }
    }

    applyTheme(saved);
    btn.addEventListener('click', () => {
        const current = html.getAttribute('data-theme');
        applyTheme(current === 'dark' ? 'light' : 'dark');
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
