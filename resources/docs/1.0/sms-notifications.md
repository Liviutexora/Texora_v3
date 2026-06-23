# SMS Notifications (Twilio)

---

Slotara can send outbound SMS to clients for booking events using each tenant's own Twilio account. SMS mirrors the existing email notification architecture and is fully opt-in at both the super-admin and tenant level.

---

## How it works

Each tenant configures their own Twilio credentials in the Business Panel. Messages are sent via Twilio's REST API and billed directly to the tenant's Twilio account. Slotara never stores or proxies Twilio API keys in plain text — credentials are encrypted at rest.

---

## Events

| Event | When sent |
|-------|-----------|
| Booking confirmation | After booking is confirmed (or after payment if pay-at-booking is enabled) |
| Appointment reminder | Day before the appointment (same schedule as email reminders) |
| Cancellation | When client or admin cancels a booking |
| Reschedule | When admin reschedules via the Bookings panel |

---

## Super Admin setup

1. Go to **Admin Panel → Settings → Notification Preferences**
2. Enable the SMS event types you want to allow platform-wide
3. Tenants can only enable events that the super-admin has permitted

---

## Tenant setup

1. Go to **Manage → Communication → SMS**
2. Enter your **Twilio Account SID**, **Auth Token**, and **From Number**
3. Enable the master **Enable SMS** switch
4. Toggle individual event types on or off

> **Note:** Clients must have a phone number on their booking to receive SMS.

---

## Guard chain

Each SMS job checks the following before sending:

1. The booking has a `phone` number
2. `TenantSmsSettings::canSend()` — master switch is on and Twilio credentials are valid
3. The per-event tenant toggle is enabled (e.g., `sms_confirmation`)
4. `NotificationPreference::isSmsEnabled()` — platform opt-in is active for this event
5. For reminders: `sms_reminder_sent_at` is not already set (idempotency guard)

---

## Cron requirement

The appointment reminder job is dispatched by the Laravel scheduler. Ensure your cron is configured:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Architecture

```
Booking confirmed
    └─► SendBookingConfirmationSms (queued job)
            ├─► TenantSmsSettings::canSend()?
            ├─► NotificationPreference check
            └─► TwilioSmsService::send()

php artisan appointments:send-reminders (scheduled daily)
    └─► SendBookingReminderSms
            ├─► skip if sms_reminder_sent_at set
            └─► TwilioSmsService::send() → sets sms_reminder_sent_at

Booking cancelled / rescheduled
    └─► SendBookingCancellationSms / SendBookingRescheduledSms
```

---

## Key files

| File | Purpose |
|------|---------|
| `app/Support/TenantSmsSettings.php` | Settings accessor / validator |
| `app/Services/TwilioSmsService.php` | Twilio API wrapper |
| `app/Services/BookingSmsMessageService.php` | Message body builder |
| `app/Jobs/SendBooking*Sms.php` | Queued jobs (one per event) |
| `app/Filament/Tenant/Pages/SmsSettings.php` | Tenant settings page |
| `tests/Feature/SmsTest.php` | Feature test suite |
