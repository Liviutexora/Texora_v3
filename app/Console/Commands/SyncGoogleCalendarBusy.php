<?php

namespace App\Console\Commands;

use App\Models\Provider;
use App\Services\GoogleCalendarSyncService;
use App\Support\TenantCalendarSettings;
use Illuminate\Console\Command;

class SyncGoogleCalendarBusy extends Command
{
    protected $signature = 'calendar:sync-busy {--days=14 : Number of days ahead to sync}';

    protected $description = 'Import busy blocks from connected Google Calendars into provider slot overrides';

    public function handle(GoogleCalendarSyncService $calendar): int
    {
        $days = max(1, (int) $this->option('days'));
        $synced = 0;

        Provider::query()
            ->where('calendar_sync_enabled', true)
            ->whereNotNull('google_refresh_token')
            ->chunkById(50, function ($providers) use ($calendar, $days, &$synced) {
                foreach ($providers as $provider) {
                    if (! TenantCalendarSettings::for((int) $provider->tenant_id)->twoWaySync()) {
                        continue;
                    }

                    if ($calendar->syncBusyFromCalendar($provider, $days)) {
                        $synced++;
                    }
                }
            });

        $this->info("Synced busy time from {$synced} provider calendar(s).");

        return self::SUCCESS;
    }
}
