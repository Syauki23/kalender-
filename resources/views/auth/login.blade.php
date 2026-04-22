@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Logo -->
        <div class="auth-logo">
            <img src="{{ asset('images/logobaru.png') }}" alt="Logo" class="auth-logo-img">
            <p class="auth-logo-sub">Sistem Manajemen Event & Kalender Korporat Terpadu</p>
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
                <label for="username" class="form-label">Username</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-user input-icon"></i>
                    <input type="text" id="username" name="username" class="form-input"
                           value="{{ old('username') }}" placeholder="Masukkan username"
                           required autocomplete="username">
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

        <!-- Quick Access -->
        <div class="auth-demo">
            <p class="auth-demo-title">Akses Cepat (Demo)</p>
            <div class="demo-accounts">
                <button type="button" class="demo-account-btn" onclick="fillDemo('admin','admin123')">
                    <i class="fa-solid fa-crown"></i>
                    <div class="demo-account-btn-text">
                        <strong>Administrator</strong>
                        <span>username: admin</span>
                    </div>
                </button>
                <button type="button" class="demo-account-btn" onclick="fillDemo('it','it123')">
                    <i class="fa-solid fa-pen-nib"></i>
                    <div class="demo-account-btn-text">
                        <strong>Event Editor (IT)</strong>
                        <span>username: it</span>
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
function fillDemo(user, pass) {
    document.getElementById('username').value = user;
    document.getElementById('password').value = pass;
    document.getElementById('username').focus();
}
</script>
@endpush
