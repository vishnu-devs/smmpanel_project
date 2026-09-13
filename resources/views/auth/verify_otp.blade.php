@extends('layouts.guest')

@section('title', 'Two Factor Verification - ' . App\Models\Setting::get('site_name', 'RishiSMM'))

@section('content')
    <div class="auth-card glass animate-fade-in">
        <div class="auth-logo">
            <div class="logo-icon" style="background: transparent; box-shadow: none; border-radius: 0;"><img src="{{ App\Models\Setting::getLogoUrl() }}" alt="{{ App\Models\Setting::getSiteName() }}" style="width: 100%; height: 100%; object-fit: contain;"></div>
            <div class="logo-text" style="font-size: 1.8rem;">{{ App\Models\Setting::getSiteName() }}</div>
        </div>
        
        <div class="auth-header">
            <h2>Two-Factor Auth</h2>
            <p>Enter the 6-digit verification code sent to your registered email address.</p>
        </div>

        @if(session('info'))
            <div class="alert alert-info" style="text-align: left; margin-bottom: 1.5rem; font-size: 0.85rem;">
                <i class="fa-solid fa-circle-info"></i>
                <div>{{ session('info') }}</div>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success" style="text-align: left; margin-bottom: 1.5rem; font-size: 0.85rem;">
                <i class="fa-solid fa-circle-check"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error" style="text-align: left; margin-bottom: 1.5rem; font-size: 0.85rem;">
                <i class="fa-solid fa-circle-xmark"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('login.verify.otp.submit') }}" method="POST">
            @csrf
            
            <div class="form-group" style="text-align: left; margin-bottom: 2rem;">
                <label for="otp" class="form-label">Verification Code</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-key" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" name="otp" id="otp" class="form-control" placeholder="123456" pattern="[0-9]{6}" maxlength="6" required style="padding-left: 45px; font-weight: bold; letter-spacing: 4px; text-align: center; font-size: 1.25rem;">
                </div>
            </div>

            <button type="submit" class="btn-gradient" style="width: 100%; padding: 14px; font-size: 1rem; font-weight: 600;">
                Verify Code
            </button>
        </form>

        <form action="{{ route('login.verify.otp.resend') }}" method="POST" style="margin-top: 1.5rem;">
            @csrf
            <button type="submit" class="btn-outline" style="width: 100%; padding: 10px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: none; background: transparent; cursor: pointer; color: var(--text-secondary);">
                <i class="fa-solid fa-arrow-rotate-right"></i> Resend Verification Code
            </button>
        </form>

        <p style="margin-top: 2rem; font-size: 0.95rem; color: var(--text-secondary);">
            Back to <a href="{{ route('login') }}" class="text-gradient" style="font-weight: 600; text-decoration: none;">Sign In</a>
        </p>
    </div>
@endsection
