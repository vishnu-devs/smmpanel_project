<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/v1',
            'api/v1/*',
            'api/v2',
            'api/v2/*',
            'payment/webhook/*',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // Global Security Headers & License Verification Middleware
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);
        $middleware->append(\App\Http\Middleware\VerifyProjectLicense::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($e instanceof \Illuminate\Session\TokenMismatchException || 
                ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() === 419) ||
                ($e->getPrevious() instanceof \Illuminate\Session\TokenMismatchException)) {
                
                if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'Session expired. Please reload and try again.'], 419);
                }
                
                return redirect()->route('login')->with('error', 'Your session has expired. Please log in again.');
            }
        });
    })->create();
