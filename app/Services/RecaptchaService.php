<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class RecaptchaService
{
    /**
     * Check if reCAPTCHA is enabled and configured
     */
    public function isEnabled(): bool
    {
        $enabled = Setting::get('google_recaptcha_enabled', false);
        $siteKey = Setting::get('google_recaptcha_site_key');
        $secretKey = Setting::get('google_recaptcha_secret_key');
        
        return $enabled && $siteKey && $secretKey;
    }

    /**
     * Get reCAPTCHA site key
     */
    public function getSiteKey(): ?string
    {
        return Setting::get('google_recaptcha_site_key');
    }

    /**
     * Verify reCAPTCHA response
     *
     * @param Request $request
     * @return array ['success' => bool, 'message' => string|null]
     */
    public function verify(Request $request): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => true, // Skip verification if not enabled
                'message' => null,
            ];
        }

        $recaptchaResponse = $request->input('g-recaptcha-response');

        if (!$recaptchaResponse) {
            return [
                'success' => false,
                'message' => 'Please complete the reCAPTCHA verification.',
            ];
        }

        try {
            $secretKey = Setting::get('google_recaptcha_secret_key');
            $verifyUrl = Setting::get('google_recaptcha_verify_url', 'https://www.google.com/recaptcha/api/siteverify');

            $response = Http::asForm()->post($verifyUrl, [
                'secret' => $secretKey,
                'response' => $recaptchaResponse,
                'remoteip' => $request->ip(),
            ]);

            $result = $response->json();

            if (!isset($result['success']) || !$result['success']) {
                $errorCodes = $result['error-codes'] ?? [];
                Log::warning('reCAPTCHA verification failed', [
                    'ip' => $request->ip(),
                    'errors' => $errorCodes,
                ]);

                return [
                    'success' => false,
                    'message' => 'reCAPTCHA verification failed. Please try again.',
                ];
            }

            // v3 returns a score (0.0–1.0). Reject likely-bot submissions below 0.5.
            if (isset($result['score']) && $result['score'] < 0.5) {
                Log::warning('reCAPTCHA v3 score too low', [
                    'ip'     => $request->ip(),
                    'score'  => $result['score'],
                    'action' => $result['action'] ?? null,
                ]);

                return [
                    'success' => false,
                    'message' => 'reCAPTCHA verification failed. Please try again.',
                ];
            }

            return [
                'success' => true,
                'message' => null,
            ];
        } catch (Exception $e) {
            Log::error('reCAPTCHA verification error', [
                'ip' => $request->ip(),
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred during reCAPTCHA verification. Please try again.',
            ];
        }
    }

    /**
     * Validate reCAPTCHA and return validation errors if failed
     *
     * @param Request $request
     * @return array Validation errors array (empty if valid)
     */
    public function validate(Request $request): array
    {
        $result = $this->verify($request);

        if (!$result['success']) {
            return ['recaptcha' => $result['message']];
        }

        return [];
    }
}

