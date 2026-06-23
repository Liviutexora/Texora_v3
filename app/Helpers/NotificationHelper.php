<?php

namespace App\Helpers;

use App\Models\NotificationPreference;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class NotificationHelper
{
    /**
     * Send an in-app (database) notification to a single user.
     *
     * Optionally pass a $url to render a "View" action button inside the
     * notification — clicking it navigates to the relevant resource page.
     *
     * Uses notifyNow() so the record is written synchronously regardless of
     * the queue driver (Filament's DatabaseNotification implements ShouldQueue,
     * which means sendToDatabase() queues the write — notifyNow() bypasses that).
     */
    public static function send(
        int $receiverId,
        string $heading,
        string $message,
        ?string $url = null,
    ): void {
        $user = User::find($receiverId);

        if (! $user) {
            return;
        }

        $notification = Notification::make()
            ->title($heading)
            ->body($message)
            ->iconColor('success')
            ->success();

        if ($url) {
            $notification->actions([
                Action::make('view')
                    ->label(__('View'))
                    ->url($url),
            ]);
        }

        // notifyNow() forces synchronous write — bypasses ShouldQueue
        $user->notifyNow($notification->toDatabase());
    }

    /**
     * Send an email to all tenant users who have opted in to $event (email = true).
     */
    public static function sendEmailToTenantUsers(
        string $event,
        int $tenantId,
        string $subjectFallback,
        string $bodyFallback,
        array $placeholders = [],
        ?string $templateSlug = null
    ): void {
        $users = NotificationPreference::query()
            ->where('permission_name', $event)
            ->where('email', true)
            ->whereIn('user_id', User::where('tenant_id', $tenantId)->select('id'))
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter(fn ($u) => $u?->email);

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            try {
                \App\Services\EmailTemplateService::sendWithLayoutFallback(
                    to: $user->email,
                    subjectFallback: $subjectFallback,
                    bodyFallback: $bodyFallback,
                    placeholders: $placeholders,
                    templateSlug: $templateSlug,
                );
            } catch (\Throwable) {
                // Non-critical — never propagate
            }
        }
    }

    /**
     * Send an in-app web notification to all tenant users who have
     * opted in to $event (web_notification = true).
     *
     * Optionally pass a $url to render a "View" action button linking to
     * the relevant booking / resource page in the tenant panel.
     *
     * Uses notifyNow() — bypasses ShouldQueue, writes synchronously.
     */
    public static function sendToTenantWebUsers(
        string $event,
        int $tenantId,
        string $heading,
        string $message,
        ?string $url = null,
    ): void {
        $optedInUserIds = NotificationPreference::query()
            ->where('permission_name', $event)
            ->where('web_notification', true)
            ->whereIn('user_id', User::where('tenant_id', $tenantId)->select('id'))
            ->pluck('user_id');

        if ($optedInUserIds->isEmpty()) {
            return;
        }

        $notification = Notification::make()
            ->title($heading)
            ->body($message)
            ->iconColor('success')
            ->success();

        if ($url) {
            $notification->actions([
                Action::make('view')
                    ->label(__('View'))
                    ->url($url),
            ]);
        }

        // notifyNow() forces synchronous write — no queue worker needed
        User::whereIn('id', $optedInUserIds)
            ->get()
            ->each(fn ($user) => $user->notifyNow($notification->toDatabase()));
    }
}
