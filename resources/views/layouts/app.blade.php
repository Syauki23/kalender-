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
    <!-- ─── MOBILE TOP BAR (visible only on small screens) ─────────────────────── -->
    <div class="mobile-topbar" id="mobileTopbar">
        <button class="mobile-hamburger" id="mobileMenuBtn" aria-label="Open menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="mobile-topbar-brand">
            <img src="{{ asset('images/logobaru.png') }}" alt="Logo" style="height: 30px;">
        </div>
        <button class="theme-toggle mobile-theme-btn" id="themeToggleMobile" title="Toggle Dark Mode">
            <i class="fa-solid fa-sun" id="themeIconLightMobile"></i>
            <i class="fa-solid fa-moon" id="themeIconDarkMobile"></i>
        </button>
    </div>
    <!-- ─── MOBILE SIDEBAR OVERLAY ─────────────────────────────────────────────── -->
    <div class="mobile-sidebar-overlay" id="mobileSidebarOverlay"></div>

    <!-- ─── APP SIDEBAR ────────────────────────────────────────────────────────── -->
    <aside class="app-sidebar" id="appSidebar">
        <div class="app-sidebar-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; gap: 0.5rem;">
            <div class="app-sidebar-logo" style="display: flex; align-items: center; gap: 0.8rem; flex: 1; overflow: hidden; padding: 0;">
                <img src="{{ asset('images/logobaru.png') }}" alt="Logo" style="height: 32px; flex-shrink: 0;">
                <span class="brand-text" style="font-weight: 800; font-size: 0.9rem; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ config('app.name', 'Kalender') }}
                </span>
            </div>
            <button id="sidebarToggle" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); cursor: pointer; color: rgba(255,255,255,0.6); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
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
            <a href="{{ route('dashboard') }}?modal=users" class="app-sidebar-link" id="manageUsersBtnSidebar">
                <i class="fa-solid fa-users-gear"></i>
                <span>Kelola Akun</span>
            </a>
            <a href="{{ route('dashboard') }}?modal=depts" class="app-sidebar-link" id="manageDeptsBtnSidebar">
                <i class="fa-solid fa-building-user"></i>
                <span>Master Departemen</span>
            </a>
            @endif
            @if(Auth::user()->canManageGlobal())
            <a href="{{ route('whatsapp-contacts.view') }}" class="app-sidebar-link {{ request()->routeIs('whatsapp-contacts.*') ? 'active' : '' }}">
                <i class="fa-brands fa-whatsapp"></i>
                <span>Kontak WA (Per DPT)</span>
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
                <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode" style="width: 100%; border-radius: 12px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08); color: rgba(255,255,255,0.6);">
                    <i class="fa-solid fa-sun" id="themeIconLight"></i>
                    <i class="fa-solid fa-moon" id="themeIconDark"></i>
                    <span style="margin-left: 0.5rem; font-size: 0.85rem; font-weight: 600;" class="theme-label">Tema</span>
                </button>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm" style="width: 100%; justify-content: center; background: rgba(239, 68, 68, 0.12); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 12px;">
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
        @if(!request()->routeIs('login'))
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
        @endif
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
        
        // Update all toggle icons (Sidebar, Guest Navbar & Mobile)
        const iconsLight = document.querySelectorAll('#themeIconLight, #themeIconLightGuest, #themeIconLightMobile');
        const iconsDark = document.querySelectorAll('#themeIconDark, #themeIconDarkGuest, #themeIconDarkMobile');
        
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
    document.querySelectorAll('#themeToggle, #themeToggleGuest, #themeToggleMobile').forEach(btn => {
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
    if (nav) {
        if (window.scrollY > 10) nav.classList.add('scrolled');
        else nav.classList.remove('scrolled');
    }
});

// ─── Mobile Sidebar Toggle ────────────────────────────────────────────────────
(function() {
    const sidebar = document.getElementById('appSidebar');
    const overlay = document.getElementById('mobileSidebarOverlay');
    const openBtn = document.getElementById('mobileMenuBtn');
    if (!sidebar || !openBtn) return;

    function openMobileSidebar() {
        sidebar.classList.add('mobile-open');
        if (overlay) overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileSidebar() {
        sidebar.classList.remove('mobile-open');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    openBtn.addEventListener('click', openMobileSidebar);
    if (overlay) overlay.addEventListener('click', closeMobileSidebar);

    // Close sidebar when clicking a nav link on mobile
    sidebar.querySelectorAll('.app-sidebar-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) closeMobileSidebar();
        });
    });
})();
</script>

@stack('scripts')
</body>
</html>
