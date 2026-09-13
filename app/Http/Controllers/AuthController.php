<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin() 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $loginInput = trim((string) ($request->input('email') ?? $request->input('login') ?? ''));

        $request->validate([
            'password' => 'required',
        ]);

        if (empty($loginInput)) {
            return back()->withErrors([
                'email' => 'Please enter your username or email address.',
            ])->onlyInput('email', 'login');
        }

        // 1. Google reCAPTCHA Server-side Verification (if enabled)
        $recaptchaToken = $request->input('g-recaptcha-response');
        if (!\App\Services\RecaptchaService::verify($recaptchaToken, 'login', $request->ip())) {
            return back()->withErrors([
                'email' => 'Google reCAPTCHA verification failed. Please try again.',
            ])->onlyInput('email', 'login');
        }

        $user = User::where('email', $loginInput)->orWhere('name', $loginInput)->first();

        // 2. If email or username does not exist
        if (!$user) {
            ActivityLog::log('failed_login_no_account', ['login' => $loginInput]);
            return back()->withErrors([
                'email' => 'No account found with this username or email. Please check your credentials or create a new account.',
            ])->onlyInput('email', 'login');
        }

        // 3. If account is suspended
        if ($user->status === 'suspended') {
            ActivityLog::log('failed_login_suspended', ['login' => $loginInput]);
            return back()->withErrors([
                'email' => 'Your account has been suspended. Please contact support.',
            ])->onlyInput('email', 'login');
        }

        // 4. If password is incorrect
        if (!Hash::check($request->input('password'), $user->password)) {
            ActivityLog::log('failed_login_bad_password', ['login' => $loginInput]);
            return back()->withErrors([
                'password' => 'Incorrect password. Please verify your password and try again.',
            ])->onlyInput('email', 'login');
        }

        // 5. Check if 2FA is forced
        if (\App\Models\Setting::get('force_2fa', 'disabled') === 'enabled') {
            $otp = (string) random_int(100000, 999999);
            session([
                'login_2fa_user_id' => $user->id,
                'login_2fa_otp' => $otp,
                'login_2fa_expiry' => now()->addMinutes(10),
                'login_2fa_remember' => $request->has('remember'),
                'login_2fa_attempts' => 0,
                'login_2fa_resends' => 0,
                'login_2fa_resend_cooldown' => now()->addSeconds(60),
            ]);

            // Send OTP email
            \App\Models\Setting::sendEmail(
                $user->email,
                "Login Verification Code",
                "RishiSMM Login Verification",
                "A login attempt was made on your account. Please use the following verification code to complete the process. It is valid for 10 minutes.",
                ['otp' => $otp]
            );

            return redirect()->route('login.verify.otp')->with('info', 'A verification code has been sent to your registered email address.');
        }

        // 6. Successful Login
        $request->session()->regenerate();
        Auth::login($user, $request->has('remember'));
        ActivityLog::log('login', ['email' => $user->email]);
        
        // Clean stale intended URL pointing to auth/login routes to prevent redirect loops back to login page
        $intended = session('url.intended');
        if ($intended && (str_contains($intended, '/login') || str_contains($intended, '/register') || str_contains($intended, '/auth'))) {
            session()->forget('url.intended');
        }

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }
        return redirect()->intended(route('dashboard'));
    }

    public function showVerifyOtp()
    {
        if (!session()->has('login_2fa_user_id')) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }
        return view('auth.verify_otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);

        if (!session()->has('login_2fa_user_id') || !session()->has('login_2fa_otp')) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $userId = session('login_2fa_user_id');
        $sessionOtp = (string) session('login_2fa_otp');
        $expiry = session('login_2fa_expiry');
        $remember = session('login_2fa_remember', false);
        $attempts = (int) session('login_2fa_attempts', 0);

        // Check expiration
        if (!$expiry || now()->gt($expiry)) {
            session()->forget([
                'login_2fa_user_id',
                'login_2fa_otp',
                'login_2fa_expiry',
                'login_2fa_remember',
                'login_2fa_attempts',
                'login_2fa_resends',
                'login_2fa_resend_cooldown',
            ]);
            return redirect()->route('login')->withErrors(['otp' => 'Verification code expired. Please login again.']);
        }

        // Constant-time OTP comparison
        if (!hash_equals($sessionOtp, (string) $request->otp)) {
            $attempts++;
            session(['login_2fa_attempts' => $attempts]);

            if ($attempts >= 5) {
                // Invalidate full 2FA session after 5 consecutive failed attempts
                session()->forget([
                    'login_2fa_user_id',
                    'login_2fa_otp',
                    'login_2fa_expiry',
                    'login_2fa_remember',
                    'login_2fa_attempts',
                    'login_2fa_resends',
                    'login_2fa_resend_cooldown',
                ]);
                return redirect()->route('login')->withErrors(['email' => 'Too many failed attempts. Please login again.']);
            }

            $remaining = 5 - $attempts;
            return back()->withErrors(['otp' => "Invalid verification code. ({$remaining} attempts remaining)"]);
        }

        $user = User::findOrFail($userId);

        // Login user
        Auth::login($user, $remember);
        
        // Clear 2FA session variables
        session()->forget([
            'login_2fa_user_id',
            'login_2fa_otp',
            'login_2fa_expiry',
            'login_2fa_remember',
            'login_2fa_attempts',
            'login_2fa_resends',
            'login_2fa_resend_cooldown',
        ]);

        $request->session()->regenerate();
        ActivityLog::log('login_2fa_success', ['email' => $user->email]);

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }
        return redirect()->intended(route('dashboard'));
    }

    public function resendOtp()
    {
        if (!session()->has('login_2fa_user_id') || !session()->has('login_2fa_otp')) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        // Check 60-second cooldown
        $cooldown = session('login_2fa_resend_cooldown');
        if ($cooldown && now()->lt($cooldown)) {
            $secondsRemaining = now()->diffInSeconds($cooldown, false);
            if ($secondsRemaining > 0) {
                return back()->withErrors(['otp' => "Please wait {$secondsRemaining} seconds before requesting another code."]);
            }
        }

        // Check Max Resends (Maximum 3 resends per session)
        $resends = (int) session('login_2fa_resends', 0);
        if ($resends >= 3) {
            session()->forget([
                'login_2fa_user_id',
                'login_2fa_otp',
                'login_2fa_expiry',
                'login_2fa_remember',
                'login_2fa_attempts',
                'login_2fa_resends',
                'login_2fa_resend_cooldown',
            ]);
            return redirect()->route('login')->withErrors(['email' => 'Too many verification code requests. Please login again.']);
        }

        $userId = session('login_2fa_user_id');
        $user = User::findOrFail($userId);

        $otp = (string) random_int(100000, 999999);
        $resends++;

        session([
            'login_2fa_otp' => $otp,
            'login_2fa_expiry' => now()->addMinutes(10),
            'login_2fa_resends' => $resends,
            'login_2fa_resend_cooldown' => now()->addSeconds(60),
            'login_2fa_attempts' => 0, // Reset attempt counter on fresh OTP
        ]);

        \App\Models\Setting::sendEmail(
            $user->email,
            "New Login Verification Code",
            "RishiSMM Login Verification",
            "A request to resend the login verification code was made. Please use the following code to verify your identity. It is valid for 10 minutes.",
            ['otp' => $otp]
        );

        return back()->with('success', 'A new verification code has been sent to your email.');
    }

    public function checkReferralCode(Request $request)
    {
        $code = trim((string)$request->query('code', ''));
        if (empty($code)) {
            return response()->json(['valid' => false, 'message' => '']);
        }

        $code = strtoupper($code);
        $referrer = User::where('referral_code', $code)->first(['id', 'name', 'status']);

        if (!$referrer || $referrer->status === 'suspended') {
            return response()->json([
                'valid' => false,
                'message' => '✕ Invalid referral code',
            ]);
        }

        if (Auth::check() && Auth::id() === $referrer->id) {
            return response()->json([
                'valid' => false,
                'message' => '✕ You cannot use your own referral code',
            ]);
        }

        return response()->json([
            'valid' => true,
            'referrer_name' => $referrer->name,
            'message' => '✓ Valid referral code - Referred by ' . e($referrer->name),
        ]);
    }

    public function showRegister(Request $request)
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin() 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('dashboard');
        }

        if ($request->has('ref') && !empty($request->input('ref'))) {
            $refCode = strtoupper(trim($request->input('ref')));
            if (User::where('referral_code', $refCode)->exists()) {
                session([
                    'registration_referral_code' => $refCode,
                    'referral_code' => $refCode,
                ]);
            }
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        // 1. Google reCAPTCHA Server-side Verification (if enabled)
        $recaptchaToken = $request->input('g-recaptcha-response');
        if (!\App\Services\RecaptchaService::verify($recaptchaToken, 'register', $request->ip())) {
            return back()->withErrors([
                'email' => 'Google reCAPTCHA verification failed. Please try again.',
            ])->withInput($request->except(['password', 'password_confirmation']));
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'whatsapp' => 'nullable|string|max:20',
            'referral_code' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'terms' => 'required|accepted',
        ], [
            'terms.required' => 'You must read and agree to the Terms & Conditions and Privacy Policy to register.',
            'terms.accepted' => 'You must read and agree to the Terms & Conditions and Privacy Policy to register.',
        ]);

        $refInput = $request->input('referral_code', $request->input('ref'));
        $refCode = !empty($refInput) ? strtoupper(trim($refInput)) : session('registration_referral_code', session('referral_code'));

        $ownReferralCode = \App\Services\ReferralService::generateUniqueReferralCode();

        $user = DB::transaction(function () use ($request, $ownReferralCode) {
            return User::create([
                'name' => $request->name,
                'email' => $request->email,
                'whatsapp' => $request->whatsapp,
                'password' => Hash::make($request->password),
                'balance' => 0.0000,
                'role' => 'user',
                'api_key' => Str::random(40),
                'status' => 'active',
                'referral_code' => $ownReferralCode,
            ]);
        });

        // Process referral link association if provided
        if (!empty($refCode)) {
            \App\Services\ReferralService::processRegistrationReferral($user, $refCode, $request);
            session()->forget(['registration_referral_code', 'referral_code']);
        }

        ActivityLog::log('register', ['user_id' => $user->id, 'email' => $user->email]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Registration successful! Welcome to RishiSMM.');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::log('logout', ['email' => Auth::user()->email]);
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function profile()
    {
        return view('user.profile', ['user' => Auth::user()]);
    }

    public function profileUpdate(Request $request)
    {
        $user = Auth::user();
        
        $rules = [
            'name' => 'required|string|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
        ];

        // If user is admin, they can edit/update their email id
        if ($user->isAdmin()) {
            $rules['email'] = 'required|string|email|max:255|unique:users,email,' . $user->id;
        }

        $request->validate($rules);

        $user->name = $request->name;
        $user->whatsapp = $request->whatsapp;
        $user->balance_reminder_unsubscribed = !$request->has('balance_reminder_subscribed');
        
        if ($user->isAdmin()) {
            $user->email = $request->email;
        }
        
        $passwordChanged = false;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $passwordChanged = true;
        }
        
        $user->save();

        ActivityLog::log('profile_update', ['password_changed' => $passwordChanged]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function generateApiKey(Request $request)
    {
        $user = Auth::user();
        $newKey = Str::random(40);
        $user->api_key = $newKey;
        $user->save();

        ActivityLog::log('api_key_regenerate');

        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'status' => 'success',
                'message' => 'New API Key generated successfully.',
                'api_key' => $newKey,
            ]);
        }

        return back()->with('success', 'New API Key generated successfully.');
    }

    public function redirectToGoogle(Request $request)
    {
        if (\App\Models\Setting::get('google_login_status') !== 'enabled') {
            return redirect()->route('login')->with('error', 'Google login is currently disabled.');
        }

        // Store / preserve referral code before redirecting out to Google OAuth
        $refCode = $request->input('ref', $request->input('referral_code'));
        if (!empty($refCode)) {
            $refCode = strtoupper(trim($refCode));
            if (User::where('referral_code', $refCode)->exists()) {
                session([
                    'registration_referral_code' => $refCode,
                    'referral_code' => $refCode,
                ]);
            }
        } elseif (session()->has('registration_referral_code') || session()->has('referral_code')) {
            $existingCode = session('registration_referral_code', session('referral_code'));
            if (User::where('referral_code', $existingCode)->exists()) {
                session([
                    'registration_referral_code' => $existingCode,
                    'referral_code' => $existingCode,
                ]);
            }
        }

        if ($request->has('from_app')) {
            session(['from_app' => true]);
        }
        return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        if (\App\Models\Setting::get('google_login_status') !== 'enabled') {
            return redirect()->route('login')->with('error', 'Google login is currently disabled.');
        }

        try {
            /** @var \Laravel\Socialite\Contracts\User $googleUser */
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->user();
            
            $googleId = (string)$googleUser->getId();
            $googleEmail = strtolower(trim((string)$googleUser->getEmail()));

            if (empty($googleId) || empty($googleEmail)) {
                return redirect()->route('login')->with('error', 'Incomplete Google profile data returned.');
            }

            // Check if Google provided email_verified claim
            $rawPayload = $googleUser->user ?? [];
            if (isset($rawPayload['email_verified']) && !filter_var($rawPayload['email_verified'], FILTER_VALIDATE_BOOLEAN)) {
                return redirect()->route('login')->with('error', 'Your Google account email is not verified.');
            }

            // Check if user is already logged in (Linking Google Account flow)
            if (Auth::check()) {
                $user = Auth::user();
                
                $existing = User::where('google_id', $googleId)->where('id', '!=', $user->id)->first();
                if ($existing) {
                    return redirect()->route('profile')->with('error', 'This Google account is already linked to another user account.');
                }
                
                $user->google_id = $googleId;
                $user->save();
                
                ActivityLog::log('google_link_success', ['email' => $user->email]);
                return redirect()->route('profile')->with('success', 'Google account linked successfully!');
            }

            // Check if user already exists by google_id or verified email
            $user = User::where('google_id', $googleId)
                ->orWhere('email', $googleEmail)
                ->first();

            if ($user) {
                // If user exists but google_id isn't linked yet, link it now
                if (!$user->google_id) {
                    $user->google_id = $googleId;
                }
                if (empty($user->api_key)) {
                    $user->api_key = Str::random(40);
                }
                if (empty($user->referral_code)) {
                    $user->referral_code = \App\Services\ReferralService::generateUniqueReferralCode();
                }
                $user->save();

                // Existing user logging in via Google: DO NOT overwrite or change referred_by!
                session()->forget(['registration_referral_code', 'referral_code']);
            } else {
                // Register a NEW user via Google
                $ownReferralCode = \App\Services\ReferralService::generateUniqueReferralCode();
                $refCode = session('registration_referral_code', session('referral_code'));

                $user = DB::transaction(function () use ($googleUser, $googleEmail, $googleId, $ownReferralCode) {
                    return User::create([
                        'name' => $googleUser->getName() ?: 'Google User',
                        'email' => $googleEmail,
                        'whatsapp' => null,
                        'password' => Hash::make(Str::random(24)), // Random secure password
                        'google_id' => $googleId,
                        'balance' => 0.0000,
                        'role' => 'user',
                        'api_key' => Str::random(40),
                        'status' => 'active',
                        'referral_code' => $ownReferralCode,
                    ]);
                });

                // Process referral association for new user if referral code exists
                if (!empty($refCode)) {
                    \App\Services\ReferralService::processRegistrationReferral($user, $refCode, request());
                    session()->forget(['registration_referral_code', 'referral_code']);
                }

                ActivityLog::log('user_register_google', ['email' => $user->email]);

                // Send welcome email on first time registration via Google
                $siteName = \App\Models\Setting::get('site_name', 'RishiSMM');
                $subject = "Welcome to {$siteName}!";
                $title = "Welcome, " . htmlspecialchars($user->name) . "!";
                $messageBody = "Thank you for joining <strong>{$siteName}</strong>. We are thrilled to welcome you to our SMM platform.<br><br>" .
                               "You can start boosting your social media accounts instantly by adding funds and selecting from our premium automated services.<br><br>" .
                               "If you have any questions or require custom support, don't hesitate to reach out to our team at any time.";
                
                \App\Models\Setting::sendEmail(
                    $user->email,
                    $subject,
                    $title,
                    $messageBody,
                    [
                        'btnText' => 'Go to Dashboard',
                        'btnUrl' => route('dashboard')
                    ]
                );
            }

            if ($user->status !== 'active') {
                return redirect()->route('login')->with('error', 'Your account is suspended.');
            }

            Auth::login($user, true);
            request()->session()->regenerate();

            ActivityLog::log('login_google', ['email' => $user->email]);

            // Handle Mobile Native App Redirection
            if (session('from_app') || request()->has('from_app')) {
                session()->forget('from_app');
                $token = $user->api_key;
                $name = urlencode($user->name);
                $email = urlencode($user->email);
                $balance = $user->balance;
                return redirect("rishismm://auth?token={$token}&name={$name}&email={$email}&balance={$balance}");
            }

            return redirect()->intended('/dashboard')->with('success', 'Logged in successfully via Google!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Auth Error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Failed to authenticate with Google. Please try again.');
        }
    }

    public function showForgotPassword()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && $user->status !== 'suspended') {
            $token = Password::broker()->createToken($user);
            $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

            $siteName = \App\Models\Setting::get('site_name', 'RishiSMM');
            $subject = "Reset Your Password - {$siteName}";
            $title = "Password Reset Request";
            $messageBody = "Hello <strong>" . htmlspecialchars($user->name) . "</strong>,<br><br>" .
                           "We received a request to reset your account password on <strong>{$siteName}</strong>.<br>" .
                           "Click the button below to set a new password. This link will expire in 60 minutes.<br><br>" .
                           "If you did not request a password reset, no further action is required.";

            \App\Models\Setting::sendEmail(
                $user->email,
                $subject,
                $title,
                $messageBody,
                [
                    'btnText' => 'Reset Password',
                    'btnUrl' => $resetUrl
                ]
            );

            ActivityLog::log('password_reset_request', ['email' => $user->email]);
        }

        // Generic response to prevent account enumeration
        return back()->with('status', 'If an account exists for this email address, a password reset link has been sent.');
    }

    public function showResetPassword($token, Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->input('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->remember_token = Str::random(60);
                $user->save();

                ActivityLog::log('password_reset_success', ['email' => $user->email]);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Your password has been reset successfully! Please log in with your new password.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
