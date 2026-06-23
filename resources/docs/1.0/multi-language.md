# Multi-Language Support

---

- [Overview](#section-overview)
- [Supported Languages](#section-languages)
- [Managing Enabled Languages](#section-manage-languages)
- [Switching the Language](#section-switching)
- [Where It Applies](#section-scope)
- [Adding or Modifying Translations](#section-customize)

<a name="section-overview"></a>
## Overview

<larecipe-badge type="success" rounded>New in v1.2</larecipe-badge>

Slotara ships with **complete UI translations for 8 popular languages**. Every panel — Super Admin, Business, and the public-facing booking pages — is fully translated. Users switch language with a single click from the header; the choice is stored in their session and takes effect immediately without a page reload.

<img src="/docs/screenshots/admin-language-switcher-open.png" alt="Language switcher dropdown showing all 8 supported languages" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

---

<a name="section-languages"></a>
## Supported Languages

| Code | Language | Native Name |
|------|----------|-------------|
| `en` | English | English |
| `es` | Spanish | Español |
| `de` | German | Deutsch |
| `fr` | French | Français |
| `ar` | Arabic | العربية |
| `ru` | Russian | Русский |
| `zh` | Chinese (Simplified) | 中文 |
| `hi` | Hindi | हिन्दी |

All 8 languages cover the full interface: navigation, form labels, validation messages, button text, notifications, and email template subject lines.

---

<a name="section-manage-languages"></a>
## Managing Enabled Languages

<larecipe-badge type="success" rounded>Admin → Settings → System → Language Management</larecipe-badge>

Super admins can control which languages are active platform-wide from a single checkbox list.

**To change enabled languages:**

1. Go to **Admin → Settings → System**.
2. Scroll to the **Language Management** section.
3. Check or uncheck languages as needed — at least one must remain checked.
4. Click **Save Settings**.

The change takes effect immediately across every panel and public page without any cache clear or deploy. Disabled languages disappear from:

- The **language switcher** in the Admin Panel, Business Panel, and front-end nav.
- The **Default Language** selector in a business's own settings.
- The `locale.switch` endpoint (a request to switch to a disabled locale is silently rejected).

> {info} Disabling a language only hides it from the UI — its translation files remain on disk. Re-enabling it restores it immediately.

> {warning} If a user has a disabled language saved as their profile locale, they will fall back to the app default (`en`) on their next session.

---

<a name="section-switching"></a>
## Switching the Language

The **language switcher** lives in the top-right header of every panel (shown as a two-letter locale code, e.g. **EN**, **ES**, **DE**).

1. Click the locale badge in the header.
2. A dropdown lists all available languages.
3. Click any language — the panel switches instantly.

<img src="/docs/screenshots/business-language-switcher-open.png" alt="Language switcher in the Business Panel showing available languages" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

The selected language is stored in the user's session, so it persists across page navigations until changed or until the session expires.

---

<a name="section-scope"></a>
## Where It Applies

| Area | Translated? |
|------|-------------|
| Super Admin Panel (`/admin`) | ✅ Full UI |
| Business Panel (`/manage`) | ✅ Full UI |
| Public Booking Page (`/{slug}`) | ✅ Full UI |
| Email notifications | ✅ Subject lines |
| Validation error messages | ✅ Yes |
| API responses | — (English only) |

> {info} The language switcher controls the **panel UI language**. It does not change the content your business has entered (service names, descriptions, etc.) — those are business-controlled.

---

<a name="section-customize"></a>
## Adding or Modifying Translations

All translation strings live in `lang/` under a sub-directory per locale:

```
lang/
  en/          ← English (default)
  es/          ← Spanish
  de/          ← German
  fr/          ← French
  ar/          ← Arabic
  ru/          ← Russian
  zh/          ← Chinese
  hi/          ← Hindi
```

To **edit an existing translation**, open the relevant file (e.g. `lang/fr/booking.php`) and change the value string. Clear the config cache after saving:

```bash
php artisan config:clear
php artisan cache:clear
```

To **add a new language**:

1. Create a new directory under `lang/` using the [ISO 639-1 code](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes) (e.g. `lang/pt/`).
2. Copy all files from `lang/en/` into the new directory and translate the values.
3. Register the locale in `config/app.php` under `available_locales`:

```php
'available_locales' => ['en', 'es', 'de', 'fr', 'ar', 'ru', 'zh', 'hi', 'pt'],
```

4. Clear caches and the new language will appear in the switcher automatically.

> {primary} **RTL support** — Arabic (`ar`) is automatically rendered right-to-left. Any locale declared as RTL in `config/app.php` (`rtl_locales`) will receive the same treatment.
