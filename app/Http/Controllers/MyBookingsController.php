<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Jobs\SendBookingCancellationEmail;
use App\Jobs\SendBookingCancellationSms;
use App\Models\Setting;
use App\Models\SlotReservation;
use App\Services\BookingPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MyBookingsController extends Controller
{
    public function index()
    {
        // Super-admin client impersonation: serve data for the impersonated client
        $impersonatedId = session('impersonate_client_id');
        if ($impersonatedId && auth()->user()?->hasRole('super_admin')) {
            $user = \App\Models\User::findOrFail($impersonatedId);
        } else {
            $user = auth()->user();
        }

        $email = $user->email;

        $base = SlotReservation::withoutGlobalScope('tenant')
            ->where('email', $email)
            ->with(['service', 'tenant'])
            ->orderBy('date')
            ->orderBy('start_time');

        $upcoming = (clone $base)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('date', '>=', now()->toDateString())
            ->get();

        $past = (clone $base)
            ->where(function ($q) {
                $q->where('date', '<', now()->toDateString())
                  ->orWhereIn('status', ['cancelled', 'completed', 'no_show']);
            })
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->limit(20)
            ->get();

        return view('pages.my-bookings', [
            'user'            => $user,
            'groupedUpcoming' => $upcoming->groupBy(fn ($b) => $b->tenant?->name ?? 'Unknown Business'),
            'groupedPast'     => $past->groupBy(fn ($b) => $b->tenant?->name ?? 'Unknown Business'),
        ]);
    }

    public function cancel(Request $request, string $token)
    {
        // Block cancellations during admin impersonation
        if (session('impersonate_client_id') && auth()->user()?->hasRole('super_admin')) {
            return response()->json(['error' => __('Cancellations are disabled while impersonating a client.')], 403);
        }

        $user    = auth()->user();
        $booking = SlotReservation::withoutGlobalScope('tenant')
            ->with(['tenant', 'service'])
            ->where('cancellation_token', $token)
            ->where('email', $user->email)   // only own bookings
            ->firstOrFail();

        if (! in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json(['error' => __('This booking can no longer be cancelled.')], 422);
        }

        // Check per-tenant allow_client_cancellation setting (default: true)
        $allowed = (bool) Setting::get("tenant_{$booking->tenant_id}_allow_client_cancellation", true);
        if (! $allowed) {
            return response()->json(['error' => __('This business does not allow online cancellations. Please contact them directly.')], 403);
        }

        $reason = trim($request->input('reason', '')) ?: __('Cancelled by client');

        // Use DB::table() to bypass the demo-mode Eloquent.saving block
        DB::table('slot_reservations')->where('id', $booking->id)->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancelled_by'        => $user->id,
            'cancellation_reason' => $reason,
            'cancellation_token'  => null,
            'updated_at'          => now(),
        ]);
        $booking->refresh();

        app(BookingPaymentService::class)->maybeDeleteCalendar($booking);

        try {
            SendBookingCancellationEmail::dispatch($booking)->afterCommit();
            SendBookingCancellationSms::dispatch($booking)->afterCommit();
        } catch (\Throwable) {}

        try {
            $url = rescue(fn () => route('filament.tenant.resources.bookings.view', ['record' => $booking->id]), null);
            NotificationHelper::sendToTenantWebUsers(
                'booking_cancelled',
                $booking->tenant_id,
                __('Booking Cancelled'),
                __('Booking #:id cancelled by :name', ['id' => $booking->id, 'name' => $booking->name]) . ($reason !== __('Cancelled by client') ? " — \"{$reason}\"" : ''),
                $url,
            );
        } catch (\Throwable) {}

        // Email opted-in tenant users (owner/staff) about the cancellation
        try {
            $e = fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
            $dashboardUrl  = rescue(fn () => route('filament.manage.pages.dashboard'), '');
            $cancelledMsg  = __('A booking has been cancelled by the client.');
            $viewDashboard = __('View in Dashboard');
            NotificationHelper::sendEmailToTenantUsers(
                event: 'booking_cancelled',
                tenantId: $booking->tenant_id,
                subjectFallback: __('Booking Cancelled') . " — {$booking->service?->name} by {$booking->name}",
                bodyFallback: <<<HTML
<p>Hi,</p>
<p>{$cancelledMsg}</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr><td style="padding:6px 0;color:#6b7280;">Booking #</td><td><strong>{{BOOKING_ID}}</strong></td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Client</td><td>{{CLIENT_NAME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Service</td><td>{{SERVICE_NAME}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Date</td><td>{{BOOKING_DATE}}</td></tr>
    <tr><td style="padding:6px 0;color:#6b7280;">Time</td><td>{{BOOKING_TIME}}</td></tr>
</table>
<p><a href="{{DASHBOARD_URL}}" style="display:inline-block;padding:10px 20px;background:#7c3aed;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">{$viewDashboard}</a></p>
HTML,
                placeholders: [
                    'BOOKING_ID'    => '#' . $booking->id,
                    'CLIENT_NAME'   => $e($booking->name ?? ''),
                    'SERVICE_NAME'  => $booking->service?->name ?? '',
                    'BOOKING_DATE'  => $booking->date?->format('D, d M Y') ?? '',
                    'BOOKING_TIME'  => substr($booking->start_time ?? '', 0, 5),
                    'DASHBOARD_URL' => $dashboardUrl,
                ],
            );
        } catch (\Throwable) {}

        return response()->json(['success' => true, 'booking_id' => $booking->id]);
    }
}
