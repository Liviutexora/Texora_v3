<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\PasswordPolicyRulesHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
                'current_password' => ['required', 'current_password'],
                // 'password' => ['required', Password::defaults(), 'confirmed'],
                'password' => PasswordPolicyRulesHelper::rules(),
            ],
            PasswordPolicyRulesHelper::messages()
        );

        $user = $request->user();

        if ($user->isPasswordInHistory($validated['password'])) {
            throw ValidationException::withMessages([
                'updatePassword' => [__('You cannot reuse a recent password. Please choose a different one.')],
            ]);
        }

        $user->updatePassword($validated['password']);

        return back()->with('status', 'password-updated');
    }
}
