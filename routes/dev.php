<?php

use App\Events\NewNotification;
use App\Helpers\NotificationHelper;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Mail\TestEmail;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/test/notification', function () {
    NotificationHelper::send(
        receiverId: 2,
        heading: 'Account Approved',
        message: 'Your account has been successfully approved by the admin.',
    );

    return 'Test notification sent.';
})->middleware('auth');

Route::get('/test/notification-seen/{id}', function ($id) {
    $notification = Notification::find($id);
    if ($notification) {
        $notification->update(['is_seen' => true]);

        return 'Notification marked as seen.';
    }

    return 'Notification not found.';
})->middleware('auth');

Route::get('/test/artisan', function () {
    Artisan::call('shield:generate', [
        '--all' => true,
        '--panel' => 'admin',
        '--no-interaction' => true,
    ]);

    return 'Shield permissions generated.';
})->middleware(['auth', EnsureSuperAdmin::class]);

Route::get('/livewire-sync', function () {
    try {
        Artisan::call('vendor:publish', [
            '--tag' => 'livewire:assets',
            '--force' => true,
        ]);

        return 'Livewire assets published successfully.';
    } catch (\Exception $e) {
        return 'Error: '.$e->getMessage();
    }
})->middleware(['auth', EnsureSuperAdmin::class]);

Route::get('/test-broadcast', function () {
    abort_unless(auth()->check(), 403, 'Login first');
    event(new NewNotification(auth()->id(), 'Hello from Echo!'));

    return 'Broadcasted successfully.';
})->middleware('auth');

Route::get('/send-test-email', function () {
    $testEmail = config('mail.test_email', 'test@example.com');
    $data = ['name' => 'Test User'];
    Mail::to($testEmail)->send(new TestEmail($data));

    return "Test email sent to {$testEmail}.";
})->middleware('auth');

Route::get('/admin/run-booking-demo', function () {
    Artisan::call('db:seed', [
        '--class' => \Database\Seeders\BookingSaasSeeder::class,
        '--force' => true,
    ]);

    return 'Booking demo seeder executed successfully.';
})->middleware(['auth', EnsureSuperAdmin::class])
    ->name('admin.run-booking-demo');
