# Web Server Setup

---

- [Nginx](#section-nginx)
- [Apache](#section-apache)
- [Shared Hosting (cPanel)](#section-cpanel)
- [Queue Worker (Supervisor)](#section-queue)
- [Cron Job (Scheduler)](#section-cron)

> {danger.fa-lock} Always point your document root to the **`public/`** directory — never the project root. Exposing `.env`, `app/`, or `database/` files is a critical security risk.

---

<a name="section-nginx"></a>
## Nginx

<larecipe-badge type="success" rounded>Recommended</larecipe-badge>

Create `/etc/nginx/sites-available/slotara`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/slotara/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable and reload:

```bash
ln -s /etc/nginx/sites-available/slotara /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
```

**Add HTTPS with Let's Encrypt (free):**

```bash
apt install certbot python3-certbot-nginx
certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Certbot auto-renews the certificate and updates your Nginx config.

---

<a name="section-apache"></a>
## Apache

Create `/etc/apache2/sites-available/slotara.conf`:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/slotara/public

    <Directory /var/www/slotara/public>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/slotara-error.log
    CustomLog ${APACHE_LOG_DIR}/slotara-access.log combined
</VirtualHost>
```

```bash
a2ensite slotara.conf
a2enmod rewrite headers
systemctl reload apache2
```

The project includes a `public/.htaccess` file that handles URL rewriting. Ensure `AllowOverride All` is set or Laravel routes won't work.

> {warning.fa-exclamation-triangle} If you see `404 Not Found` on all routes except `/`, `mod_rewrite` is not enabled or `AllowOverride All` is missing from your Apache config.

---

<a name="section-cpanel"></a>
## Shared Hosting (cPanel)

<larecipe-badge type="info" rounded>Shared Hosting</larecipe-badge>

cPanel doesn't let you change the document root, so you need to restructure the files:

**Step 1 — Upload project files:**

```
public_html/        ← contents of slotara/public/ go here
slotara_app/       ← everything else goes here (outside public_html)
  ├── app/
  ├── bootstrap/
  ├── config/
  ├── database/
  ├── vendor/
  └── ...
```

**Step 2 — Edit `public_html/index.php`:**

```php
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Change both paths to point to your app folder
require __DIR__.'/../slotara_app/vendor/autoload.php';

$app = require_once __DIR__.'/../slotara_app/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
```

**Step 3 — Edit `public_html/.htaccess`:**
No changes needed — the existing `.htaccess` handles rewriting.

> {primary.fa-info-circle} Also update `APP_ROOT` in your `.env` to point to the `slotara_app/` folder if path helpers behave unexpectedly.

---

<a name="section-queue"></a>
## Queue Worker (Supervisor)

<larecipe-badge type="danger" rounded>Required for Production</larecipe-badge>

All emails are sent via the queue. Without a running worker, they pile up and never send.

Install Supervisor:

```bash
apt install supervisor
```

Create `/etc/supervisor/conf.d/slotara-worker.conf`:

```ini
[program:slotara-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/slotara/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/slotara/storage/logs/worker.log
stopwaitsecs=3600
```

Start the worker:

```bash
supervisorctl reread
supervisorctl update
supervisorctl start slotara-worker:*
```

Check status:

```bash
supervisorctl status
```

> {primary.fa-refresh} After each deployment, run `supervisorctl restart slotara-worker:*` to reload new code into the worker process.

---

<a name="section-cron"></a>
## Cron Job (Scheduler)

Slotara uses Laravel's task scheduler for reminder emails. Add one cron entry to run the scheduler every minute:

```bash
crontab -e
```

Add this line:

```bash
* * * * * cd /var/www/slotara && php artisan schedule:run >> /dev/null 2>&1
```

On cPanel, add a cron job via **Cron Jobs** in the control panel with the same command.

> Without the cron job, booking reminders will not run.
