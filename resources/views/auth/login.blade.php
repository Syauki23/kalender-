@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Logo -->
        <div class="auth-logo">
            <div class="auth-logo-icon">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <h1 class="auth-logo-title">Kalendar<span class="brand-accent">Pro</span></h1>
            <p class="auth-logo-sub">Masuk ke sistem kalender korporat</p>
        </div>

        <!-- Error -->
        @if($errors->any())
        <div class="alert alert-danger">
            <i class="fa-solid fa-triangle-exclamation"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <!-- Form -->
        <form action="{{ route('login.post') }}" method="POST" class="auth-form" id="loginForm">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" class="form-input"
                           value="{{ old('email') }}" placeholder="contoh@perusahaan.com"
                           required autocomplete="email">
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" class="input-toggle-pass" id="togglePass" tabindex="-1">
                        <i class="fa-solid fa-eye" id="passIcon"></i>
                    </button>
                </div>
            </div>

            <div class="form-check-row">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" class="checkbox-input">
                    <span class="checkbox-custom"></span>
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-full" id="loginBtn">
                <span id="loginBtnText"><i class="fa-solid fa-right-to-bracket"></i> Masuk</span>
                <span id="loginBtnLoading" style="display:none;"><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span>
            </button>
        </form>

        <!-- Demo Accounts -->
        <div class="auth-demo">
            <p class="auth-demo-title">Akun Demo</p>
            <div class="demo-accounts">
                <button class="demo-account-btn" onclick="fillDemo('admin@kalender.com','admin123')">
                    <i class="fa-solid fa-crown"></i>
                    <div>
                        <strong>Admin</strong>
                        <span>admin@kalender.com</span>
                    </div>
                </button>
                <button class="demo-account-btn" onclick="fillDemo('editor@kalender.com','editor123')">
                    <i class="fa-solid fa-pen-nib"></i>
                    <div>
                        <strong>Editor</strong>
                        <span>editor@kalender.com</span>
                    </div>
                </button>
            </div>
        </div>

        <div class="auth-footer">
            <a href="{{ route('home') }}" class="auth-back-link">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Kalender Publik
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Toggle password visibility
document.getElementById('togglePass').addEventListener('click', function() {
    const pw = document.getElementById('password');
    const icon = document.getElementById('passIcon');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        pw.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});

// Loading state on submit
document.getElementById('loginForm').addEventListener('submit', function() {
    document.getElementById('loginBtnText').style.display = 'none';
    document.getElementById('loginBtnLoading').style.display = 'inline';
    document.getElementById('loginBtn').disabled = true;
});

// Fill demo credentials
function fillDemo(email, pass) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = pass;
    document.getElementById('email').focus();
}
</script>
@endpush
