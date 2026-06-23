<?php

use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\CheckPasswordExpiry;
use App\Http\Middleware\EnsureTwoFactorAuthenticated;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\InstallerMiddleware;
use App\Http\Middleware\RemoveIndexPhpFromUrls;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetTenantFromUser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: file_exists(__DIR__.'/../routes/api.php') ? __DIR__.'/../routes/api.php' : null,
        commands: file_exists(__DIR__.'/../routes/console.php') ? __DIR__.'/../routes/console.php' : null,
        health: '/up',
        then: function () {
            // Add your code here
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(InstallerMiddleware::class);

        // Stripe webhooks must not have CSRF verification — Stripe sends raw POST
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
            'razorpay/webhook',
            'paypal/webhook',
            'paddle/webhook',
        ]);

        $middleware->api([
            ForceJsonResponse::class,
        ]);
        $middleware->web([
            SecurityHeaders::class,
            RemoveIndexPhpFromUrls::class,
            ForceHttps::class,
            EnsureTwoFactorAuthenticated::class,
            EnsureEmailIsVerified::class,
            CheckMaintenanceMode::class,
            SetTenantFromUser::class,
            CheckPasswordExpiry::class,
            SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Consistent JSON envelope for all API errors
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }
        });
    })->create();