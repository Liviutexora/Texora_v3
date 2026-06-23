# Google Calendar Sync

---

Slotara provides two-way Google Calendar synchronisation. New bookings create calendar events for the provider; when providers block time on their Google Calendar, those slots are automatically blocked in Slotara's booking engine.

---

## Outbound sync (Slotara → Google)

When a booking is **confirmed**, **rescheduled**, or **cancelled**:

1. `SyncBookingToGoogleCalendar` job is dispatched (if calendar sync is enabled for that provider)
2. Creates, updates, or deletes the Google Calendar event via the Calendar API v3
3. Stores `google_calendar_event_id` on the booking record for future updates

---

## Inbound sync (Google → Slotara)

```bash
php artisan calendar:sync-busy
```

- Fetches all calendar events for each connected provider from the Google Calendar Events API
- Writes busy blocks to `provider_slot_overrides` (type `blocked`)
- The slot availability engine excludes these blocks when generating bookable slots
- Runs **hourly** via Laravel scheduler

---

## Setup

### 1. Super Admin — configure Google OAuth

1. Create a project at [console.cloud.google.com](https://console.cloud.google.com) → **APIs & Services → Credentials**
2. Enable the **Google Calendar API**
3. Create an **OAuth 2.0 Client ID** (Web application type)
4. Add your domain's callback URL: `https://yourdomain.com/manage/calendar/oauth/callback`
5. Copy the **Client ID** and **Client Secret** to your `.env`:

```env
GOOGLE_CALENDAR_CLIENT_ID=your-client-id
GOOGLE_CALENDAR_CLIENT_SECRET=your-client-secret
```

### 2. Tenant — enable calendar sync

1. Go to **Manage → Integrations → Calendar Sync**
2. Enable **Google Calendar sync**
3. Each provider clicks **Connect Google** and authorises their own account

### 3. Cron — schedule inbound sync

Ensure the Laravel scheduler is running (the `calendar:sync-busy` command is scheduled hourly):

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## OAuth flow

```
Provider clicks "Connect Google"
    └─► GET /manage/calendar/oauth?provider_id={id}
            └─► Google OAuth consent screen
                    └─► GET /manage/calendar/oauth/callback
                            └─► Stores access_token + refresh_token on provider record
                                (encrypted; tokens refreshed automatically before API calls)
```

---

## Disconnect

From **Manage → Communication → Calendar Sync**, click **Disconnect** next to a provider to revoke the integration. The provider's tokens are cleared from the database. Future bookings will not sync until the provider reconnects.

---

## Key files

| File | Purpose |
|------|---------|
| `app/Support/TenantCalendarSettings.php` | Settings accessor |
| `app/Services/GoogleCalendarSyncService.php` | Google Calendar API wrapper |
| `app/Jobs/SyncBookingToGoogleCalendar.php` | Outbound create/update job |
| `app/Jobs/DeleteBookingFromGoogleCalendar.php` | Outbound delete job |
| `app/Console/Commands/SyncGoogleCalendarBusy.php` | Inbound busy-block command |
| `app/Filament/Tenant/Pages/CalendarSettings.php` | Tenant settings page |
| `app/Http/Controllers/GoogleCalendarController.php` | OAuth redirect + callback |
| `tests/Feature/GoogleCalendarSyncTest.php` | Feature test suite |
