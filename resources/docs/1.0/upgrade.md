# Upgrading

---

- [Before You Upgrade](#section-before)
- [Upgrade Steps](#section-steps)
- [Files to Preserve](#section-preserve)
- [After Upgrade Checklist](#section-checklist)

<a name="section-before"></a>
## Before You Upgrade

> {danger.fa-ban} **Always back up first.** Run a full database dump and archive your `storage/` folder before replacing any files. An upgrade cannot be undone without a backup.

```bash
# Quick database dump before upgrading (replace with your credentials)
mysqldump -u your_db_user -p your_db_name > slotara_backup_$(date +%Y%m%d).sql
```

On **cPanel / shared hosting**, use **phpMyAdmin → Export** or your host's backup tool instead.

---

<a name="section-steps"></a>
## Upgrade Steps

**1. Download** the new version from CodeCanyon → Your Purchases → Download

**2. Enable maintenance mode** to prevent bookings during the upgrade:

```bash
php artisan down --message="Scheduled maintenance. Back shortly." --retry=60
```

**3. Replace application files** — overwrite everything **except** the files listed in the [Files to Preserve](#section-preserve) section below.

```bash
# Extract new version (adjust path as needed)
unzip slotara-v*.zip -d /tmp/slotara-new

# Rsync everything except preserved files
rsync -av --exclude='.env' \
          --exclude='storage/' \
          --exclude='public/storage' \
          /tmp/slotara-new/ /var/www/slotara/
```

**4. Install updated dependencies:**

```bash
composer install --no-dev --optimize-autoloader
```

Pre-compiled frontend assets are included in `public/build/` — no Node.js required for a standard upgrade. Only run the following if this version's release notes specifically mention frontend changes:

```bash
npm install && npm run build
```

**5. Run database migrations** to apply any schema changes:

```bash
php artisan migrate --force
```

**6. Clear and rebuild all caches:**

```bash
php artisan optimize:clear
php artisan optimize
```

**7. Restart queue workers** so they pick up the new code:

```bash
supervisorctl restart slotara-worker:*
```

**8. Bring the site back up:**

```bash
php artisan up
```

**9. Verify** — visit your site and admin panel, test a booking, and check the queue is running:

```bash
supervisorctl status
php artisan queue:monitor
```

---

<a name="section-preserve"></a>
## Files to Preserve

<larecipe-badge type="danger" rounded>Never Overwrite These</larecipe-badge>

| File / Directory | Reason |
|---|---|
| `.env` | All your environment config — DB creds, API keys, etc. |
| `storage/` | Uploaded files, logs, compiled views, framework cache |
| `public/storage` | Symlink to `storage/app/public` — re-run `storage:link` if it breaks |
| Any custom views in `resources/views/` | Custom design changes you've made |

> {primary.fa-lightbulb-o} If you've customised any Blade views (e.g. email templates, booking page layout), keep a diff of your changes before upgrading so you can reapply them if the base view changed.

---

<a name="section-checklist"></a>
## After Upgrade Checklist

Run through this after every upgrade:

```
☐  Site loads without errors
☐  Admin panel accessible at /admin
☐  Business panel accessible at /manage
☐  Public booking page loads at /{tenant-slug}
☐  Queue worker running (supervisorctl status)
☐  Test booking created successfully
☐  Test email received
☐  Storage link works (uploaded images display)
☐  Cache cleared (php artisan optimize)
```

> {warning.fa-exclamation-triangle} If you see a blank white page after upgrading, run `php artisan optimize:clear` and check `storage/logs/laravel.log` for the error.
