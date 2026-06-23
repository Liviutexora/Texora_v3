<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class TakeScreenshots extends Command
{
    protected $signature   = 'screenshots:take {--url=http://localhost:8000 : Base URL of the running server}';
    protected $description = 'Capture UI screenshots for the documentation';

    private string $chrome  = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
    private string $outDir  = 'public/docs/screenshots';
    private string $baseUrl = 'http://localhost:8000';

    public function handle(): int
    {
        $this->baseUrl = rtrim($this->option('url'), '/');

        if (! file_exists($this->chrome)) {
            $this->error('Google Chrome not found at: ' . $this->chrome);
            $this->line('Install Chrome or pass --url if running elsewhere.');
            return 1;
        }

        if (! is_dir($this->outDir)) {
            mkdir($this->outDir, 0755, true);
        }

        // ── Build a session cookie for the tenant owner ────────────────
        $this->info('🔑  Creating authenticated session…');
        $sessionId = $this->createSession();
        if (! $sessionId) {
            $this->error('Could not create a session. Make sure a tenant owner user exists.');
            return 1;
        }

        // Write a helper PHP file that sets the cookie then redirects
        $helperPath = public_path('_ss_helper.php');
        file_put_contents($helperPath, $this->helperScript($sessionId));

        $this->info('📸  Capturing screenshots…');

        try {
            // ── Public pages ─────────────────────────────────────────────
            $this->capture('/lenslife-studio',      'booking-page-desktop.png', 1440, 900);
            $this->captureWithAuth($helperPath, '/lenslife-studio', 'booking-page-lumina.png', 1440, 900, $sessionId);

            // ── Receipt ──────────────────────────────────────────────────
            $token = \App\Models\SlotReservation::withoutGlobalScope('tenant')
                ->whereNotNull('cancellation_token')->value('cancellation_token');
            if ($token) {
                $this->capture("/booking/{$token}/receipt", 'booking-receipt.png', 1200, 900);
            }

            // ── Business panel ────────────────────────────────────────────
            $this->captureAuth('/manage/payment-settings',   'business-payment-settings.png',   1440, 820, $sessionId, $helperPath);
            $this->captureAuth('/manage/sms-settings',       'business-sms-settings.png',        1440, 820, $sessionId, $helperPath);
            $this->captureAuth('/manage/calendar-settings',  'business-calendar-settings.png',   1440, 820, $sessionId, $helperPath);
            $this->captureAuth('/manage/bookings',           'business-bookings-list.png',        1440, 820, $sessionId, $helperPath);
            $this->captureAuth('/manage',                    'business-dashboard.png',            1440, 820, $sessionId, $helperPath);
        } finally {
            @unlink($helperPath);
        }

        $this->newLine();
        $this->info('✅  Screenshots saved to ' . $this->outDir . '/');
        return 0;
    }

    private function createSession(): ?string
    {
        $user = User::whereHas('roles', fn ($q) => $q->where('name', 'owner'))
            ->orderBy('id')
            ->first();

        if (! $user) {
            $user = User::whereNotNull('tenant_id')->orderBy('id')->first();
        }

        if (! $user) {
            return null;
        }

        // Start a real session via an HTTP request to the helper endpoint
        // (bootstrapping auth in CLI context uses a different guard driver)
        return md5($user->id . config('app.key') . now()->timestamp);
    }

    private function helperScript(string $sessionId): string
    {
        $uid = User::whereHas('roles', fn ($q) => $q->where('name', 'owner'))
            ->orderBy('id')->value('id') ?? 1;

        return <<<PHP
        <?php
        define('LARAVEL_START', microtime(true));
        require __DIR__.'/../vendor/autoload.php';
        \$app = require_once __DIR__.'/../bootstrap/app.php';
        \$kernel = \$app->make(\Illuminate\Contracts\Http\Kernel::class);
        \$request = \Illuminate\Http\Request::createFromGlobals();
        \$app->instance('request', \$request);
        \$app->boot();
        \$user = \App\Models\User::find({$uid});
        if (\$user) {
            \Illuminate\Support\Facades\Auth::guard('web')->login(\$user, true);
            session()->save();
        }
        \$to = \$_GET['to'] ?? '/manage/payment-settings';
        header('Location: ' . \$to);
        exit;
        PHP;
    }

    private function capture(string $path, string $filename, int $w, int $h): void
    {
        $this->runChrome($this->baseUrl . $path, $filename, $w, $h);
    }

    private function captureWithAuth(string $helperPath, string $path, string $filename, int $w, int $h, string $sessionId): void
    {
        $this->runChrome($this->baseUrl . $path, $filename, $w, $h);
    }

    private function captureAuth(string $path, string $filename, int $w, int $h, string $sessionId, string $helperPath): void
    {
        // Navigate via helper (sets session), then capture the target page
        $helperUrl = $this->baseUrl . '/_ss_helper.php?to=' . urlencode($path);
        $this->runChrome($helperUrl, $filename, $w, $h);
    }

    private function runChrome(string $url, string $filename, int $w, int $h): void
    {
        $outFile = base_path($this->outDir . '/' . $filename);
        $profile = sys_get_temp_dir() . '/slotara-ss-' . getmypid() . '-' . uniqid();

        $process = new Process([
            $this->chrome,
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--user-data-dir=' . $profile,
            '--window-size=' . $w . ',' . $h,
            '--screenshot=' . $outFile,
            '--hide-scrollbars',
            '--virtual-time-budget=3000',
            $url,
        ]);

        $process->setTimeout(60);
        $process->run();

        // Cleanup temp profile
        if (is_dir($profile)) {
            (new Process(['rm', '-rf', $profile]))->run();
        }

        $size = file_exists($outFile) ? round(filesize($outFile) / 1024) . 'KB' : 'FAILED';
        $status = file_exists($outFile) ? '✓' : '✗';
        $this->line("  {$status} {$filename}  ({$size})");
    }
}
