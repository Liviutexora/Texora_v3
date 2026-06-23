# Requirements

---

- [Server Requirements](#section-server)
- [PHP Extensions](#section-php)
- [Optional but Recommended](#section-optional)
- [Stripe Account](#section-stripe)
- [Google reCAPTCHA v3](#section-recaptcha)

<a name="section-server"></a>
## Server Requirements

<larecipe-badge type="danger" rounded>Required</larecipe-badge>

| Requirement | Minimum | Recommended |
|---|---|---|
| PHP | **8.2** | 8.3+ |
| MySQL | **8.0** | 8.0+ |
| MariaDB | 10.6+ | 10.11+ |
| Web Server | Apache / Nginx | **Nginx** |
| Composer | 2.x | latest |
| Node.js | 18 | **20+** |

> {warning.fa-exclamation-triangle} **PHP 8.1 and below are not supported.** Slotara uses PHP 8.2+ features including readonly properties and first-class callable syntax.

---

<a name="section-php"></a>
## PHP Extensions

All of the following extensions must be enabled on your server:

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:0.6rem;margin:1.25rem 0">
  <div style="display:flex;align-items:center;gap:0.5rem;padding:0.55rem 0.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.5rem;font-size:0.82rem;font-weight:600;color:#166534">
    <span style="color:#22c55e;font-size:0.9rem">✓</span> BCMath
  </div>
  <div style="display:flex;align-items:center;gap:0.5rem;padding:0.55rem 0.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.5rem;font-size:0.82rem;font-weight:600;color:#166534">
    <span style="color:#22c55e;font-size:0.9rem">✓</span> Ctype
  </div>
  <div style="display:flex;align-items:center;gap:0.5rem;padding:0.55rem 0.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.5rem;font-size:0.82rem;font-weight:600;color:#166534">
    <span style="color:#22c55e;font-size:0.9rem">✓</span> cURL
  </div>
  <div style="display:flex;align-items:center;gap:0.5rem;padding:0.55rem 0.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.5rem;font-size:0.82rem;font-weight:600;color:#166534">
    <span style="color:#22c55e;font-size:0.9rem">✓</span> DOM
  </div>
  <div style="display:flex;align-items:center;gap:0.5rem;padding:0.55rem 0.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.5rem;font-size:0.82rem;font-weight:600;color:#166534">
    <span style="color:#22c55e;font-size:0.9rem">✓</span> Fileinfo
  </div>
  <div style="display:flex;align-items:center;gap:0.5rem;padding:0.55rem 0.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.5rem;font-size:0.82rem;font-weight:600;color:#166534">
    <span style="color:#22c55e;font-size:0.9rem">✓</span> JSON
  </div>
  <div style="display:flex;align-items:center;gap:0.5rem;padding:0.55rem 0.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.5rem;font-size:0.82rem;font-weight:600;color:#166534">
    <span style="color:#22c55e;font-size:0.9rem">✓</span> Mbstring
  </div>
  <div style="display:flex;align-items:center;gap:0.5rem;padding:0.55rem 0.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.5rem;font-size:0.82rem;font-weight:600;color:#166534">
    <span style="color:#22c55e;font-size:0.9rem">✓</span> OpenSSL
  </div>
  <div style="display:flex;align-items:center;gap:0.5rem;padding:0.55rem 0.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.5rem;font-size:0.82rem;font-weight:600;color:#166534">
    <span style="color:#22c55e;font-size:0.9rem">✓</span> PDO
  </div>
  <div style="display:flex;align-items:center;gap:0.5rem;padding:0.55rem 0.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.5rem;font-size:0.82rem;font-weight:600;color:#166534">
    <span style="color:#22c55e;font-size:0.9rem">✓</span> PDO_MySQL
  </div>
  <div style="display:flex;align-items:center;gap:0.5rem;padding:0.55rem 0.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.5rem;font-size:0.82rem;font-weight:600;color:#166534">
    <span style="color:#22c55e;font-size:0.9rem">✓</span> Tokenizer
  </div>
  <div style="display:flex;align-items:center;gap:0.5rem;padding:0.55rem 0.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.5rem;font-size:0.82rem;font-weight:600;color:#166534">
    <span style="color:#22c55e;font-size:0.9rem">✓</span> XML
  </div>
  <div style="display:flex;align-items:center;gap:0.5rem;padding:0.55rem 0.85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.5rem;font-size:0.82rem;font-weight:600;color:#166534">
    <span style="color:#22c55e;font-size:0.9rem">✓</span> ZIP
  </div>
</div>

**Verify your extensions with:**

```bash
php -m | grep -E "bcmath|ctype|curl|dom|fileinfo|json|mbstring|openssl|pdo|tokenizer|xml|zip"
```

You should see each extension listed in the output. Any missing extension will appear blank.

> {primary.fa-lightbulb-o} Most shared hosting plans (cPanel, Plesk) have all these extensions enabled by default for PHP 8.2+. On a VPS, install missing extensions with `apt install php8.2-{ext}`.

---

<a name="section-optional"></a>
## Optional but Recommended

<larecipe-badge type="warning" rounded>Recommended for Production</larecipe-badge>

| Component | Purpose | Without It |
|---|---|---|
| **Redis** | Faster cache & queue driver | Falls back to database driver (slower) |
| **Supervisor** | Keeps queue workers alive | Emails queue silently if worker stops |
| **SSL Certificate** | HTTPS for Stripe & reCAPTCHA | Stripe/reCAPTCHA won't work without HTTPS |
| **Amazon S3** | Cloud file storage | Files stored locally on the server |
| **SendGrid / Mailgun** | Reliable transactional email | Gmail SMTP rate-limited in production |

> **Free SSL** — Use Let's Encrypt (`certbot`) on any VPS. Most cPanel hosts include AutoSSL.

---

<a name="section-stripe"></a>
## Stripe Account

<larecipe-badge type="warning" rounded>Required for Paid Services</larecipe-badge>

Stripe is needed when any service or subscription plan has payment enabled.

1. Create a free account at [stripe.com](https://stripe.com)
2. Navigate to **Developers → API Keys**
3. Copy your **Publishable Key** (`pk_live_...`) and **Secret Key** (`sk_live_...`)
4. Go to **Developers → Webhooks → Add Endpoint**
5. Set URL to `https://yourdomain.com/stripe/webhook`
6. Copy the **Signing Secret** (`whsec_...`)

> {primary.fa-lightbulb-o} Use **test keys** (`pk_test_...` / `sk_test_...`) during development. Test card: `4242 4242 4242 4242`, any future expiry, any CVC.

---

<a name="section-recaptcha"></a>
## Google reCAPTCHA v3

<larecipe-badge type="primary" rounded>Optional</larecipe-badge>

Protects the public contact form from spam bots. Uses the **invisible v3 API** — no checkbox shown to users.

1. Go to [google.com/recaptcha/admin/create](https://www.google.com/recaptcha/admin/create)
2. Label: anything (e.g. `Slotara`)
3. **reCAPTCHA type**: choose **Score based (v3)** — not v2
4. Add your domain(s) under **Domains**
5. Submit and copy the **Site Key** and **Secret Key**
6. Paste both into **Admin → Settings → Security**

> {danger.fa-ban} **Do not use v2 keys with Slotara.** The contact form uses the v3 invisible API. v2 keys will produce an "Invalid key type" error on the contact page.
