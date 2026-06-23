<?php

namespace App\Helpers;

use App\Models\PasswordPolicy;
use App\Models\Setting;

class PasswordPolicyRulesHelper
{
    public static function rules(): array
    {
        $policy = PasswordPolicy::first();

        // If no PasswordPolicy, use settings as fallback
        if (!$policy) {
            $minLength = (int) Setting::get('password_min_length', 8);
            $rules = ['required', 'string', "min:{$minLength}"];
            
            // Add regex patterns based on settings
            $patterns = [];
            if (Setting::get('password_require_uppercase', false)) $patterns[] = '(?=.*[A-Z])';
            if (Setting::get('password_require_lowercase', false)) $patterns[] = '(?=.*[a-z])';
            if (Setting::get('password_require_numbers', false)) $patterns[] = '(?=.*\d)';
            if (Setting::get('password_require_special_chars', false)) $patterns[] = '(?=.*[@$!%*?&#])';
            
            if (!empty($patterns)) {
                $rules[] = 'regex:/^' . implode('', $patterns) . '.*/';
            }
            
            return $rules;
        }

        $rules = ['required', 'string', "min:{$policy->min_length}"];

        $patterns = [];
        if ($policy->require_uppercase) $patterns[] = '(?=.*[A-Z])';
        if ($policy->require_lowercase) $patterns[] = '(?=.*[a-z])';
        if ($policy->require_numbers) $patterns[] = '(?=.*\d)';
        if ($policy->require_special_chars) $patterns[] = '(?=.*[@$!%*?&#])';

        if (!empty($patterns)) {
            $rules[] = 'regex:/^' . implode('', $patterns) . '.*/';
        }
        return $rules;
    }

    public static function messages(): array
    {
        $policy = PasswordPolicy::getDefault() ?? PasswordPolicy::first();

        // If no PasswordPolicy, use settings as fallback
        if (!$policy) {
            $minLength = (int) Setting::get('password_min_length', 8);
            $messages = [
                'password.min' => "The password must be at least {$minLength} characters.",
            ];
            
            // Add messages for settings-based requirements
            $requireUppercase = Setting::get('password_require_uppercase', false);
            $requireLowercase = Setting::get('password_require_lowercase', false);
            $requireNumbers = Setting::get('password_require_numbers', false);
            $requireSpecialChars = Setting::get('password_require_special_chars', false);
            
            $regexMessages = [];
            if ($requireUppercase) $regexMessages[] = "The password must contain at least one uppercase letter.";
            if ($requireLowercase) $regexMessages[] = "The password must contain at least one lowercase letter.";
            if ($requireNumbers) $regexMessages[] = "The password must contain at least one number.";
            if ($requireSpecialChars) $regexMessages[] = "The password must contain at least one special character.";
            
            if (!empty($regexMessages)) {
                // Combine all messages into a single string (Laravel requires strings, not arrays)
                $messages['password.regex'] = implode(' ', $regexMessages);
            }
            
            return $messages;
        }

        $messages = [
            'password.min' => "The password must be at least {$policy->min_length} characters.",
        ];

        $regexMessages = [];
        if ($policy->require_uppercase) $regexMessages[] = "The password must contain at least one uppercase letter.";
        if ($policy->require_lowercase) $regexMessages[] = "The password must contain at least one lowercase letter.";
        if ($policy->require_numbers) $regexMessages[] = "The password must contain at least one number.";
        if ($policy->require_special_chars) $regexMessages[] = "The password must contain at least one special character.";
        
        if (!empty($regexMessages)) {
            // Combine all messages into a single string (Laravel requires strings, not arrays)
            $messages['password.regex'] = implode(' ', $regexMessages);
        }

        return $messages;
    }
}
