# Dashboard & Settings

---

- [Dashboard](#section-dashboard)
- [Settings Tabs](#section-tabs)
- [Header Actions](#section-actions)

<a name="section-dashboard"></a>
## Dashboard

The admin dashboard is your platform health snapshot. It loads automatically when you visit `/admin`.

<img src="/docs/screenshots/admin-dashboard.png" alt="Admin Dashboard — stats widgets showing total users, businesses, subscriptions, and recent activity" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

At a glance you can see:

- Total users and businesses registered
- Active subscriptions
- Recent user registrations
- Latest activity log entries

---

<a name="section-tabs"></a>
## Settings Tabs

Navigate to **Admin → Settings**. All changes save immediately with no server restart.

---

### Branding

<larecipe-badge type="primary" rounded>Admin → Settings → Branding</larecipe-badge>

Upload your logo and favicon, set the site name, and control the admin sidebar logo size. See [Configuration → Branding](/{{route}}/{{version}}/configuration#section-branding).

---

### Email

<larecipe-badge type="primary" rounded>Admin → Settings → Email</larecipe-badge>

SMTP credentials for outgoing email. Includes a **Send Test Email** form at the bottom of the tab — enter any address and click send to instantly verify delivery.

<img src="/docs/screenshots/admin-settings-email.png" alt="Admin → Settings → Email tab showing SMTP fields and Send Test Email form" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

---

### System

<larecipe-badge type="primary" rounded>Admin → Settings → System</larecipe-badge>

Maintenance mode toggle with a custom visitor message, timezone selector, date format, and time format (12h / 24h).

**Language Management** — choose which of the 8 built-in languages are active across the entire platform. Unchecked languages disappear from every language switcher (admin panel, business panel, and public pages) immediately after saving. At least one language must remain enabled. See [Multi-Language → Managing Enabled Languages](/{{route}}/{{version}}/multi-language#section-manage-languages) for full details.

**Business Registration Options** — restrict which timezones and currencies appear in the business setup wizard. Leave blank to allow all.

---

### Security

<larecipe-badge type="primary" rounded>Admin → Settings → Security</larecipe-badge>

Session timeout, login attempt limits, lockout duration, Force HTTPS toggle, and Google reCAPTCHA v3 credentials. See the dedicated [Security Logs](/{{route}}/{{version}}/admin-security) and [reCAPTCHA v3](/{{route}}/{{version}}/recaptcha) pages.

---

### Filesystem

<larecipe-badge type="primary" rounded>Admin → Settings → Storage</larecipe-badge>

Switch between local disk and Amazon S3. S3 credentials are stored write-only and never displayed after saving. See [Configuration → Filesystem](/{{route}}/{{version}}/configuration#section-filesystem).

---

### Payments

<larecipe-badge type="primary" rounded>Admin → Settings → Payments</larecipe-badge>

Stripe Publishable Key, Secret Key, and Webhook Signing Secret. See [Stripe Payments](/{{route}}/{{version}}/stripe) for full setup instructions.

---

### Password Policy

<larecipe-badge type="primary" rounded>Admin → Settings → Password Policy</larecipe-badge>

Password complexity rules, expiry, and history enforcement. See [Password Policy](/{{route}}/{{version}}/password-policy).

---

<a name="section-actions"></a>
## Header Actions

Two utility buttons appear in the Settings page header:

| Button | What It Does |
|---|---|
| **Clear Cache** | Runs `php artisan optimize:clear` — clears config, route, view, and event caches |
| **Run Queue Work** | Starts a one-off background queue worker process (useful on hosts without Supervisor) |

> {primary.fa-info-circle} Settings changes auto-clear the cache. Only run **Clear Cache** manually after editing `.env` directly or deploying new code.

> {warning.fa-cog} **Run Queue Work** starts a temporary worker. On production, use **Supervisor** for a persistent worker. See [Web Server Setup → Queue Worker](/{{route}}/{{version}}/webserver#section-queue).
