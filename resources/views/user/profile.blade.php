@extends('layouts.app')

@section('title', 'My Profile - Growinsta')
@section('page_header', 'Account Profile')

@section('content')
<div style="max-width: 1000px; margin: 0 auto;">

    <div class="card-grid">
        
        <!-- Profile Form Box -->
        <div class="glass custom-card">
            <h3 class="card-title"><i class="fa-solid fa-user-pen text-gradient"></i> Edit Profile Details</h3>
            
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    @if($user->isAdmin())
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    @else
                        <label for="email" class="form-label">Email Address (Read-only)</label>
                        <input type="email" id="email" class="form-control" value="{{ $user->email }}" readonly style="background: rgba(255,255,255,0.01); color: var(--text-muted); cursor: not-allowed;">
                    @endif
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="whatsapp" class="form-label">WhatsApp Number</label>
                    <input type="text" name="whatsapp" id="whatsapp" class="form-control" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="e.g. +91 99999 99999">
                </div>

                <div class="form-group" style="margin-top: 1.25rem;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-secondary); font-size: 0.88rem; user-select: none;">
                        <input type="checkbox" name="balance_reminder_subscribed" value="1" {{ !$user->balance_reminder_unsubscribed ? 'checked' : '' }} style="accent-color: var(--color-primary); width: 16px; height: 16px; cursor: pointer;">
                        <span><i class="fa-solid fa-bell text-gradient" style="margin-right: 4px;"></i> Receive weekly wallet balance reminder emails when inactive</span>
                    </label>
                </div>

                @if(App\Models\Setting::get('google_login_status') === 'enabled')
                    <div style="border-top: 1px dashed var(--border-color); margin: 1.5rem 0 1rem 0; padding-top: 1rem; text-align: left;">
                        <label class="form-label">Connected Social Accounts</label>
                        @if($user->google_id)
                            <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(52, 168, 83, 0.1); border: 1px solid rgba(52, 168, 83, 0.2); border-radius: var(--radius-sm); padding: 10px 15px; color: var(--color-success); font-size: 0.9rem;">
                                <span><i class="fa-brands fa-google" style="margin-right: 8px;"></i> Google Account Linked</span>
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                        @else
                            <a href="{{ route('auth.google') }}" class="btn-outline" style="width: 100%; padding: 10px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; color: var(--text-primary);">
                                <i class="fa-brands fa-google text-gradient"></i> Link Google Account
                            </a>
                        @endif
                    </div>
                @endif

                <div style="border-top: 1px dashed var(--border-color); margin: 2rem 0; padding-top: 1.5rem;">
                    <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 12px; color: var(--text-primary);">Change Password</h4>
                    <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 1.5rem;">Leave empty if you do not wish to change your current password.</p>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="btn-gradient" style="width: 100%; padding: 12px;">
                    Save Profile Changes
                </button>
            </form>
        </div>

        <!-- API Reseller Settings Box -->
        <div class="glass custom-card" style="border-top: 4px solid var(--color-info);">
            <h3 class="card-title" style="color: var(--color-info);"><i class="fa-solid fa-code"></i> Developer API Settings</h3>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">
                Your API key allows you to route orders from other panels. Keep this key safe.
            </p>

            <div style="background: rgba(0,0,0,0.2); border-radius: var(--radius-md); padding: 15px; margin-bottom: 1.5rem;">
                <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Current API Token:</span>
                
                <div style="display: flex; gap: 8px; align-items: center; margin-top: 5px;">
                    <input type="password" id="apiTokenField" class="form-control" value="{{ $user->api_key }}" readonly style="font-family: monospace; letter-spacing: 2px; padding: 10px;">
                    <button onclick="toggleKey()" class="btn-outline" style="padding: 10px 14px;" title="Show/Hide">
                        <i id="toggleEye" class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- API Actions buttons -->
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button onclick="copyKey()" class="btn-gradient" style="width: 100%; padding: 12px; font-weight: 600; color: white !important;">
                    <i class="fa-solid fa-copy" style="margin-right: 8px;"></i> Copy Key to Clipboard
                </button>
                
                <button type="button" id="regenProfileBtn" onclick="regenerateProfileApiKey()" class="btn-gradient" style="width: 100%; padding: 12px; background: var(--grad-danger); box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2);">
                    <i id="regenProfileSpin" class="fa-solid fa-arrows-rotate" style="margin-right: 8px;"></i> Regenerate API Key
                </button>
            </div>

            <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem; text-align: center;">
                <a href="{{ route('api.docs') }}" style="color: var(--color-info); text-decoration: none; font-size: 0.9rem; font-weight: 600;">
                    Read API Integration Docs <i class="fa-solid fa-arrow-up-right-from-square" style="margin-left: 5px; font-size: 0.8rem;"></i>
                </a>
            </div>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    function toggleKey() {
        const keyField = document.getElementById('apiTokenField');
        const eyeIcon = document.getElementById('toggleEye');
        if (keyField.type === 'password') {
            keyField.type = 'text';
            eyeIcon.className = 'fa-solid fa-eye-slash';
        } else {
            keyField.type = 'password';
            eyeIcon.className = 'fa-solid fa-eye';
        }
    }

    function copyKey() {
        const keyField = document.getElementById('apiTokenField');
        keyField.select();
        keyField.setSelectionRange(0, 99999);
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(keyField.value).catch(() => {
                document.execCommand('copy');
            });
        } else {
            document.execCommand('copy');
        }
        alert('API Key copied to clipboard!');
    }

    function regenerateProfileApiKey() {
        if (!confirm('Warning: Regenerating your API key will generate a brand new key and break all your existing integrations using the current key. Continue?')) {
            return;
        }

        const regenBtn = document.getElementById('regenProfileBtn');
        const regenSpin = document.getElementById('regenProfileSpin');
        const keyField = document.getElementById('apiTokenField');

        if (regenSpin) regenSpin.classList.add('fa-spin');
        if (regenBtn) regenBtn.disabled = true;

        fetch("{{ route('profile.api_key') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (regenSpin) regenSpin.classList.remove('fa-spin');
            if (regenBtn) regenBtn.disabled = false;

            if (data.status === 'success' && data.api_key) {
                keyField.value = data.api_key;
                alert('✓ New API Key generated successfully! Your key has been updated.');
            } else {
                location.reload();
            }
        })
        .catch(err => {
            if (regenSpin) regenSpin.classList.remove('fa-spin');
            if (regenBtn) regenBtn.disabled = false;
            location.reload();
        });
    }
</script>
@endsection
