<?php

namespace App\Http\Controllers;

use App\Events\ContactUsSubmitted;
use App\Models\ContactUs;
use App\Models\Setting;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContactUsController extends Controller
{
    public function index()
    {
        return view('pages.contact');
    }

    /**
     * Same defaults as theme partial `pages.partials.contact-us-form` when settings are empty.
     *
     * @return array<int, array<string, mixed>>
     */
    protected static function defaultContactFormFields(): array
    {
        return [
            ['name' => 'name', 'label' => __('Full Name'), 'type' => 'text', 'enabled' => true, 'required' => true, 'validation' => 'max:255', 'placeholder' => __('John Doe')],
            ['name' => 'email', 'label' => __('Email Address'), 'type' => 'email', 'enabled' => true, 'required' => true, 'validation' => 'max:255', 'placeholder' => __('john@example.com')],
            ['name' => 'phone', 'label' => __('Phone Number'), 'type' => 'tel', 'enabled' => true, 'required' => false, 'validation' => 'max:50', 'placeholder' => __('+1 555-000-0000')],
            ['name' => 'message', 'label' => __('Your Message'), 'type' => 'textarea', 'enabled' => true, 'required' => true, 'validation' => '', 'placeholder' => __('Write your message here...'), 'rows' => 5],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function resolveContactFormFieldsFromSettings(): array
    {
        $raw = Setting::get('contact_form_fields');
        if ($raw === null || $raw === '') {
            return self::defaultContactFormFields();
        }

        if (is_array($raw)) {
            $decoded = $raw;
        } else {
            $decoded = json_decode((string) $raw, true);
        }

        if (! is_array($decoded)) {
            return self::defaultContactFormFields();
        }

        $enabled = array_values(array_filter($decoded, function ($field) {
            if (! is_array($field) || empty($field['name'])) {
                return false;
            }
            if (! array_key_exists('enabled', $field)) {
                return true;
            }

            return filter_var($field['enabled'], FILTER_VALIDATE_BOOLEAN);
        }));

        return $enabled !== [] ? $enabled : self::defaultContactFormFields();
    }

    public function store(Request $request)
    {
        $formFields = self::resolveContactFormFieldsFromSettings();

        $validationRules = [];

        foreach ($formFields as $field) {
            if (! is_array($field)) {
                continue;
            }

            $fieldName = isset($field['name']) ? (string) $field['name'] : '';
            if ($fieldName === '') {
                continue;
            }

            $rules = [];

            $isRequired = isset($field['required']) && filter_var($field['required'], FILTER_VALIDATE_BOOLEAN);
            if ($isRequired) {
                $rules[] = 'required';
            } else {
                $rules[] = 'nullable';
            }

            $fieldType = $field['type'] ?? 'text';
            switch ($fieldType) {
                case 'email':
                    $rules[] = 'email';
                    break;
                case 'number':
                    $rules[] = 'numeric';
                    break;
                case 'url':
                    $rules[] = 'url';
                    break;
                case 'tel':
                    $rules[] = 'string';
                    break;
                default:
                    $rules[] = 'string';
                    break;
            }

            if (! empty($field['validation']) && is_string($field['validation'])) {
                $customRules = array_filter(
                    array_map('trim', explode('|', $field['validation'])),
                    static fn ($r) => $r !== ''
                );
                $rules = array_merge($rules, $customRules);
            }

            $validationRules[$fieldName] = implode('|', $rules);
        }

        if ($validationRules === []) {
            Log::warning('Contact form submit: no validation rules; using defaults.');

            foreach (self::defaultContactFormFields() as $field) {
                $fieldName = (string) ($field['name'] ?? '');
                if ($fieldName === '') {
                    continue;
                }
                $req = isset($field['required']) && filter_var($field['required'], FILTER_VALIDATE_BOOLEAN);
                $validationRules[$fieldName] = ($req ? 'required' : 'nullable').'|string';
                if (($field['type'] ?? '') === 'email') {
                    $validationRules[$fieldName] .= '|email';
                }
                if (! empty($field['validation']) && is_string($field['validation'])) {
                    $extra = array_filter(
                        array_map('trim', explode('|', $field['validation'])),
                        static fn ($r) => $r !== ''
                    );
                    if ($extra !== []) {
                        $validationRules[$fieldName] .= '|'.implode('|', $extra);
                    }
                }
            }
        }

        $validationRules['type'] = 'required|string|in:' . implode(',', array_keys(\App\Models\ContactUs::TYPE_LIST));

        $recaptchaService = app(RecaptchaService::class);
        $recaptchaErrors = $recaptchaService->validate($request);

        if ($recaptchaErrors !== []) {
            return back()->withErrors($recaptchaErrors)->withInput();
        }

        $validated = $request->validate($validationRules);

        $fillableFields = (new ContactUs)->getFillable();
        $contactData = [
            'status' => ContactUs::STATUS_NEW,
        ];

        $customFields = [];

        foreach ($validated as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }
            if (in_array($key, $fillableFields, true) && $key !== 'custom_fields') {
                $contactData[$key] = $value;
            } else {
                $customFields[] = [
                    'name' => $key,
                    'value' => is_scalar($value) || $value === null ? (string) $value : json_encode($value),
                ];
            }
        }

        if ($customFields !== []) {
            $contactData['custom_fields'] = $customFields;
        }

        $contactUs = ContactUs::create($contactData);

        // Email notifications + in-app notifications are both handled inside
        // SendContactUsEmails (dispatched via the ContactUsSubmitted event listener).
        // Do NOT send them here as well — that would cause duplicates.
        try {
            event(new ContactUsSubmitted($contactUs));
        } catch (Throwable $e) {
            Log::error('Contact form: ContactUsSubmitted failed', ['exception' => $e]);
        }

        return back()->with('success', __('Message sent successfully!'));
    }
}
