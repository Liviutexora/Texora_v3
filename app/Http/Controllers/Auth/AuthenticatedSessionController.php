<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\PasswordPolicy;
use App\Models\Setting;
use App\Services\RecaptchaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Verify reCAPTCHA if enabled
        $recaptchaErrors = app(RecaptchaService::class)->validate($request);
        if (! empty($recaptchaErrors)) {
            throw ValidationException::withMessages($recaptchaErrors);
        }

        // Get security settings from Settings (priority) or PasswordPolicy (fallback)
        $maxAttempts = (int) Setting::get('max_login_attempts', null);
        $lockoutMinutes = (int) Setting::get('lockout_duration', null);
        
        // Fallback to PasswordPolicy if settings not available
        if ($maxAttempts === null || $maxAttempts === 0) {
            $policy = PasswordPolicy::getDefault() ?? PasswordPolicy::first();
            $maxAttempts = $policy?->max_login_attempts ?? 5;
        }
        
        if ($lockoutMinutes === null || $lockoutMinutes === 0) {
            $policy = $policy ?? PasswordPolicy::getDefault() ?? PasswordPolicy::first();
            $lockoutMinutes = $policy?->lockout_duration ?? 30;
        }

        $key = Str::lower($request->input('email')) . '|' . $request->ip();

        // Check if user is locked out
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in " . ceil($seconds / 60) . " minutes.",
            ]);
        }
        // Attempt login
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($key, $lockoutMinutes * 60);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }
        

        // Successful login → clear limiter
        RateLimiter::clear($key);

        // Regenerate session
        $request->session()->regenerate();

        // Reset failed login attempts if using a User method
        if (method_exists($request->user(), 'resetFailedLoginAttempts')) {
            $request->user()->resetFailedLoginAttempts();
        }


        if ($request->user()->two_factor_enabled) {
            session(['two_factor_authenticated' => false]);
            return redirect()->route('two-factor.show');
        }

        // Optional: log session if your User model supports it
        if (method_exists($request->user(), 'logSession')) {
            $session = $request->user()->logSession(session()->getId(), $request);
            session(['user_session_id' => $session->id]);
        }

        // Check if email verification is required
        $requireEmailVerification = Setting::get('require_email_verification', false);
        
        if ($requireEmailVerification && !$request->user()->hasVerifiedEmail()) {
            // Redirect to email verification notice if not verified
            return redirect()->route('verification.notice');
        }

        // Check for custom membership intended URL first
        $intendedUrl = session()->pull('membership_intended_url');
        if ($intendedUrl && is_string($intendedUrl)) {
            return redirect($intendedUrl);
        }
        
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            $redirectTo = route('filament.admin.pages.dashboard', absolute: false);
        } elseif ($user->hasRole('tenant_owner')) {
            $redirectTo = route('filament.tenant.pages.tenant-dashboard', absolute: false);
        } elseif ($user->hasAnyRole(['staff'])) {
            // Staff members log into the tenant manage panel
            $redirectTo = route('filament.tenant.pages.tenant-dashboard', absolute: false);
        } else {
            // Regular users (booking customers) → cross-tenant My Bookings page.
            $redirectTo = route('my-bookings', absolute: false);
        }

        return redirect()->intended($redirectTo);
    }

    /**
     * Logout the user.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(base_url('/'));
    }
}
