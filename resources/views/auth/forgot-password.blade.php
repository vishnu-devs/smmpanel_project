@extends('layouts.guest')

@section('title', 'Forgot Password - ' . App\Models\Setting::get('site_name', 'RishiSMM'))

@section('content')
    <div class="auth-card glass animate-fade-in">
        <div class="auth-logo">
            <div class="logo-icon" style="background: transparent; box-shadow: none; border-radius: 0;">
                <img src="{{ App\Models\Setting::getLogoUrl() }}" alt="{{ App\Models\Setting::getSiteName() }}" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <div class="logo-text" style="font-size: 1.8rem;">{{ App\Models\Setting::getSiteName() }}</div>
        </div>
        
        <div class="auth-header">
            <h3 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 0.5rem;" class="text-gradient">Forgot Your Password?</h3>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Enter your registered email address and we'll send you a password reset link.</p>
        </div>

        @if(session('status'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div><p>{{ session('status') }}</p></div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-xmark"></i>
                <div><p>{{ session('error') }}</p></div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-xmark"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf

            <div class="form-group" style="text-align: left; margin-bottom: 1.5rem;">
                <label for="email" class="form-label">Registered Email Address</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-envelope" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" value="{{ old('email') }}" required autofocus style="padding-left: 45px;">
                </div>
            </div>

            <button type="submit" class="btn-gradient" style="width: 100%; padding: 14px; font-size: 1rem; font-weight: 700; cursor: pointer; margin-bottom: 1rem;">
                <i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Send Reset Link
            </button>
        </form>

        <p style="margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-secondary);">
            Remembered your password? <a href="{{ route('login') }}" class="text-gradient" style="font-weight: 600; text-decoration: none;">Back to Login</a>
        </p>
    </div>
@endsection
