@extends('layouts.guest')

@section('title', 'Reset Password - ' . App\Models\Setting::get('site_name', 'RishiSMM'))

@section('content')
    <div class="auth-card glass animate-fade-in">
        <div class="auth-logo">
            <div class="logo-icon" style="background: transparent; box-shadow: none; border-radius: 0;">
                <img src="{{ App\Models\Setting::getLogoUrl() }}" alt="{{ App\Models\Setting::getSiteName() }}" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <div class="logo-text" style="font-size: 1.8rem;">{{ App\Models\Setting::getSiteName() }}</div>
        </div>
        
        <div class="auth-header">
            <h3 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 0.5rem;" class="text-gradient">Reset Password</h3>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Set a new password for your account</p>
        </div>

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

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group" style="text-align: left; margin-bottom: 1.25rem;">
                <label for="email" class="form-label">Email Address</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-envelope" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" value="{{ old('email', $email) }}" required readonly style="padding-left: 45px; background: rgba(255,255,255,0.02); cursor: not-allowed;">
                </div>
            </div>

            <div class="form-group" style="text-align: left; margin-bottom: 1.25rem;">
                <label for="password" class="form-label">New Password</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-lock" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required autofocus style="padding-left: 45px;">
                </div>
            </div>

            <div class="form-group" style="text-align: left; margin-bottom: 1.75rem;">
                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-lock" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="••••••••" required style="padding-left: 45px;">
                </div>
            </div>

            <button type="submit" class="btn-gradient" style="width: 100%; padding: 14px; font-size: 1rem; font-weight: 700; cursor: pointer;">
                <i class="fa-solid fa-key" style="margin-right: 6px;"></i> Reset Password
            </button>
        </form>

        <p style="margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-secondary);">
            Remembered your password? <a href="{{ route('login') }}" class="text-gradient" style="font-weight: 600; text-decoration: none;">Back to Login</a>
        </p>
    </div>
@endsection
