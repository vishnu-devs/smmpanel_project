<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyProjectLicense
{
    /**
     * Handle an incoming request.
     * Locks panel and redirects to /license if license is invalid, expired, or bound to a different domain.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Exempt license activation routes and static assets
        if ($request->is('license') || $request->is('license/*') || $request->is('uploads/*') || $request->is('images/*')) {
            return $next($request);
        }

        try {
            $isValid = LicenseService::isLicenseValid();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("License Middleware Error: " . $e->getMessage());
            $isValid = false;
        }

        // Validate License Status
        if (!$isValid) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'System Lock: This installation requires a valid 1-Year License Key bound to domain ' . $request->getHost() . '.',
                    'redirect' => url('/license'),
                ], 403);
            }

            return redirect()->to('/license')->with('error', '⚠️ System Locked: Please enter a valid 1-Year License Key for ' . $request->getHost() . ' to unlock this panel.');
        }

        return $next($request);
    }
}
