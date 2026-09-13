@extends('layouts.guest')

@section('title', 'Unsubscribed - ' . App\Models\Setting::get('site_name', 'RishiSMM'))

@section('content')
    <div class="auth-card glass animate-fade-in" style="text-align: center; max-width: 480px; margin: 0 auto; padding: 2.5rem 2rem;">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); border: 2px solid #10b981; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
            <i class="fa-solid fa-bell-slash" style="font-size: 1.8rem; color: #10b981;"></i>
        </div>

        <h2 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 0.75rem;" class="text-gradient">Successfully Unsubscribed</h2>

        <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem;">
            You have been unsubscribed from weekly wallet balance reminder emails for <strong>{{ $userEmail }}</strong>.
        </p>

        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 2rem;">
            You can still log into your account and use your balance anytime. If you wish to re-enable notifications in the future, you can update your settings inside your account profile.
        </p>

        <a href="{{ route('login') }}" class="btn-gradient" style="display: block; width: 100%; padding: 12px; font-weight: 700; text-decoration: none; border-radius: var(--radius-md);">
            <i class="fa-solid fa-right-to-bracket" style="margin-right: 6px;"></i> Return to Login
        </a>
    </div>
@endsection
