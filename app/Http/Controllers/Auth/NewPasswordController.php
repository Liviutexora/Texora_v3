<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Validation\ValidationException;
use App\Helpers\NotificationHelper;
use App\Helpers\PasswordPolicyRulesHelper;
use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
                'token' => ['required'],
                'email' => ['required', 'email'],
                // 'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'password' => PasswordPolicyRulesHelper::rules(),
            ],
            PasswordPolicyRulesHelper::messages()
        );

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                if ($user->isPasswordInHistory($request->password)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'password' => [__('You cannot reuse a recent password. Please choose a different one.')],
                    ]);
                }

                $user->updatePassword($request->password);
                $user->forceFill(['remember_token' => Str::random(60)])->save();

                // Global system toggle — enabled by default if no pref record exists
                if (NotificationPreference::isEmailEnabled('reset_password_confirmation')) {
                    event(new PasswordReset($user));
                }

                // Always send in-app security alert to the user who just reset their password
                NotificationHelper::send(
                    receiverId: $user->id,
                    heading: __('Password Reset Successful'),
                    message: __('Your password has been changed successfully. If this was not you, contact support immediately.')
                );
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
