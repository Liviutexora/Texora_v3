<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Configure mail transport from settings
        $this->configureMailTransport();

        try {
            $request->user()->sendEmailVerificationNotification();
            return back()->with('status', 'verification-link-sent');
        } catch (TransportException $e) {
            Log::error('Failed to send email verification', [
                'user_id' => $request->user()->id,
                'user_email' => $request->user()->email,
                'error' => $e->getMessage(),
            ]);
            
            return back()->withErrors(['email' => __('Failed to send email verification. Please try again.')]);
        } catch (\Exception $e) {
            Log::error('Failed to send email verification', [
                'user_id' => $request->user()->id,
                'user_email' => $request->user()->email,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['email' => __('Failed to send email verification. Please try again.')]);
        }
    }

    /**
     * Configure mail transport from settings.
     */
    protected function configureMailTransport(): void
    {
        $mailHost = Setting::get('mail_host');
        $mailPort = Setting::get('mail_port');
        $mailUsername = Setting::get('mail_username');
        $mailPassword = Setting::get('mail_password');
        $mailEncryption = Setting::get('mail_encryption', 'tls');
        $mailFromAddress = Setting::get('mail_from_address');
        $mailFromName = Setting::get('mail_from_name');

        if (!$mailHost || !$mailPort || !$mailUsername || !$mailPassword) {
            return;
        }

        $config = [
            'transport' => 'smtp',
            'host' => $mailHost,
            'port' => $mailPort,
            'username' => $mailUsername,
            'password' => $mailPassword,
            'encryption' => $mailEncryption ?: null,
        ];
        config()->set('mail.mailers.smtp', $config);

        if ($mailFromAddress) {
            config()->set('mail.from.address', $mailFromAddress);
        }

        if ($mailFromName) {
            config()->set('mail.from.name', $mailFromName);
        }
    }
}
