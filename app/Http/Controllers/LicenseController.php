<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\LicenseService;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    /**
     * Display the System License Activation / Status Lock Page.
     */
    public function showActivationForm()
    {
        $currentDomain = LicenseService::getCurrentDomain();
        $isValid = LicenseService::isLicenseValid();

        $licenseInfo = [
            'key' => Setting::get('license_key', ''),
            'domain' => Setting::get('license_domain', ''),
            'expires_at' => Setting::get('license_expires_at', ''),
            'status' => Setting::get('license_status', 'unlicensed'),
            'is_valid' => $isValid,
            'current_domain' => $currentDomain,
        ];

        return view('license.activate', compact('licenseInfo'));
    }

    /**
     * Process License Key activation submission.
     */
    public function verifyKey(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string|max:255',
        ], [
            'license_key.required' => 'Please enter a valid 1-Year License Key.',
        ]);

        $result = LicenseService::verifyAndActivateKey($request->input('license_key'));

        if ($result['success']) {
            return redirect()->route('admin.dashboard')->with('success', $result['message']);
        }

        return back()->with('error', $result['message'])->withInput();
    }
}
