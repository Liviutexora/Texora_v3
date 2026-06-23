# Changelog

---

All notable changes to Slotara are documented here. Versioning follows [Semantic Versioning](https://semver.org/) — `MAJOR.MINOR.PATCH`.

| Type | Meaning |
|---|---|
| <larecipe-badge type="success">New</larecipe-badge> | New feature added |
| <larecipe-badge type="primary">Improved</larecipe-badge> | Enhancement to existing feature |
| <larecipe-badge type="danger">Fixed</larecipe-badge> | Bug fix |
| <larecipe-badge type="warning">Breaking</larecipe-badge> | Change that requires manual action on upgrade |
| <larecipe-badge type="info">Security</larecipe-badge> | Security patch |

---

## v1.3.0 — SMS, Payments & Google Calendar

<larecipe-badge type="warning">Released 2026-06-10</larecipe-badge>

### SMS Notifications (Twilio)

- **Twilio SMS** — tenants configure their own Twilio credentials and send booking event SMS to clients. <larecipe-badge type="success">New</larecipe-badge>
- **4 SMS events** — confirmation, appointment reminder, cancellation, and reschedule notices. <larecipe-badge type="success">New</larecipe-badge>
- **Per-event toggles** — tenants independently enable or disable each event type; super-admin sets platform-wide permissions. <larecipe-badge type="success">New</larecipe-badge>
- **Idempotent reminders** — reminder guard prevents duplicate SMS when the scheduler runs more than once. <larecipe-badge type="success">New</larecipe-badge>

### Pay-at-Booking

- **Require payment at booking** — clients pay before confirmation emails and SMS are dispatched. <larecipe-badge type="success">New</larecipe-badge>
- **4 payment gateways** — Stripe Checkout, Razorpay Orders API, PayPal Orders v2, and Paddle Billing. <larecipe-badge type="success">New</larecipe-badge>
- **Offline payment recording** — staff mark cash, card terminal, or bank transfer payments from Bookings → Payment. <larecipe-badge type="success">New</larecipe-badge>
- **Print receipts** — public printable receipt at `/booking/{token}/receipt`; no login required. <larecipe-badge type="success">New</larecipe-badge>

### Google Calendar Sync

- **Outbound sync** — bookings are created, updated, and deleted as Google Calendar events in real time. <larecipe-badge type="success">New</larecipe-badge>
- **Inbound busy-block sync** — `calendar:sync-busy` (hourly) pulls provider calendar events and blocks those slots in the booking engine. <larecipe-badge type="success">New</larecipe-badge>
- **Per-provider OAuth** — each provider connects their own Google account from Manage → Integrations → Calendar Sync. <larecipe-badge type="success">New</larecipe-badge>

---

## v1.2.0 — Multi-Language & Rebrand

<larecipe-badge type="warning">Released 2026</larecipe-badge>

### Multi-Language Support

- **8-language UI** — complete translation for English, Spanish, German, French, Arabic, Russian, Chinese (Simplified), and Hindi across all panels (Super Admin, Business, and public booking pages). <larecipe-badge type="success">New</larecipe-badge>
- **Language switcher** — one-click locale selector in the top-right header; selection persists in the user's session. <larecipe-badge type="success">New</larecipe-badge>
- **RTL support** — Arabic is rendered right-to-left automatically. <larecipe-badge type="success">New</larecipe-badge>
- **Extensible** — new locales can be added by dropping translation files into `lang/{locale}/` and registering the code in `config/app.php`. <larecipe-badge type="success">New</larecipe-badge>

### Rebrand

- **SlotLine → Slotara** — platform renamed to Slotara. <larecipe-badge type="primary">Improved</larecipe-badge>

---

## v1.1.0 — Client Portal & UX

<larecipe-badge type="warning">Released 2026</larecipe-badge>

### Client Portal

- **My Bookings page** (`/my-bookings`) — cross-business dashboard showing all upcoming and past appointments for the logged-in client, grouped by business brand. <larecipe-badge type="success">New</larecipe-badge>
- **In-app cancellation modal** — clients cancel from My Bookings without leaving the page; a modal captures an optional reason. <larecipe-badge type="success">New</larecipe-badge>
- **Cancellation reason stored** — reason is saved on the booking and shown in Business Panel → Bookings → View, and in italic on cancelled cards in My Bookings. <larecipe-badge type="success">New</larecipe-badge>
- **Allow/disallow client cancellations** — per-business toggle in Booking Behaviour settings. <larecipe-badge type="success">New</larecipe-badge>
- **Cancellation notification** — staff receive an in-app notification when a client cancels, including the reason. <larecipe-badge type="success">New</larecipe-badge>
- **Booking form pre-fill** — when a logged-in client visits any booking page, their name, email, and phone are auto-filled from their account; email is locked to prevent mismatch. <larecipe-badge type="primary">Improved</larecipe-badge>

### Navigation

- **User dropdown in nav** — logged-in users on public pages see a role-aware dropdown (avatar + name) with a link to their dashboard or My Bookings and a Sign Out button. <larecipe-badge type="success">New</larecipe-badge>
- **Multi-service total on Confirm step** — total price now correctly sums all selected services instead of showing only the first service's price. <larecipe-badge type="danger">Fixed</larecipe-badge>

---

## v1.0.0 — Initial Release

<larecipe-badge type="warning">Released 2025</larecipe-badge>

### Platform & Architecture

- **Multi-tenant SaaS architecture** — single-database tenancy with automatic Eloquent query scoping via `TenantScope`. <larecipe-badge type="success">New</larecipe-badge>
- **Super Admin Panel** (`/admin`) — Filament 5-powered admin for managing tenants, users, plans, templates, and settings. <larecipe-badge type="success">New</larecipe-badge>
- **Business Panel** (`/manage`) — Filament 5-powered tenant panel for day-to-day operations. <larecipe-badge type="success">New</larecipe-badge>
- **Public Booking Pages** — customer-facing booking flow at `/{slug}`. <larecipe-badge type="success">New</larecipe-badge>

### Booking & Services

- **Service management** — create services with name, description, duration, price, image, and provider assignment. <larecipe-badge type="success">New</larecipe-badge>
- **Provider management** — bookable team members with per-day working hours, bio, and avatar. <larecipe-badge type="success">New</larecipe-badge>
- **Staff management** — restricted-access panel users who can manage bookings but not services or settings. <larecipe-badge type="success">New</larecipe-badge>
- **Booking management** — full lifecycle (Pending → Confirmed → Completed / Cancelled / No-Show). <larecipe-badge type="success">New</larecipe-badge>
- **Manual booking creation** — staff can create bookings on behalf of clients. <larecipe-badge type="success">New</larecipe-badge>
- **iCal export** — clients receive an `.ics` link in their confirmation email. <larecipe-badge type="success">New</larecipe-badge>
- **Client tracking** — auto-built CRM from bookings: lifetime spend, top service, booking history. <larecipe-badge type="success">New</larecipe-badge>

### Payments

- **Stripe Checkout integration** — pay-before-book flow for paid services. <larecipe-badge type="success">New</larecipe-badge>
- **Webhook handling** — `checkout.session.completed` and `checkout.session.expired` events. <larecipe-badge type="success">New</larecipe-badge>
- **Subscription plans** — tiered plans shown on the registration page, linked to Stripe Price IDs. <larecipe-badge type="success">New</larecipe-badge>

### Email & Notifications

- **Email template system** — customisable transactional emails with layout/template separation and `@{{PLACEHOLDER}}` syntax. <larecipe-badge type="success">New</larecipe-badge>
- **Booking confirmation email** — sent to client on booking. <larecipe-badge type="success">New</larecipe-badge>
- **Booking cancellation email** — sent to client when booking is cancelled. <larecipe-badge type="success">New</larecipe-badge>
- **Contact inbox** — admin receives, tracks, and replies to contact form messages. <larecipe-badge type="success">New</larecipe-badge>
- **Admin notification preferences** — per-user, per-event, per-channel (Email / In-App) opt-ins. <larecipe-badge type="success">New</larecipe-badge>

### Security

- **Role & permission system** — Spatie-based with 5 roles: `super_admin`, `tenant_owner`, `provider`, `staff`, `client`. <larecipe-badge type="success">New</larecipe-badge>
- **Password policy** — configurable complexity, history, and expiry. <larecipe-badge type="success">New</larecipe-badge>
- **reCAPTCHA v3** — invisible bot protection on the contact form. <larecipe-badge type="success">New</larecipe-badge>
- **IP restrictions** — allow/deny rules for the admin panel. <larecipe-badge type="success">New</larecipe-badge>
- **Security headers** — `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `X-XSS-Protection`. <larecipe-badge type="success">New</larecipe-badge>
- **Activity logs** — every create/update/delete action recorded via Spatie Activity Log. <larecipe-badge type="success">New</larecipe-badge>
- **Login activity** — all authentication attempts logged with IP, user agent, and outcome. <larecipe-badge type="success">New</larecipe-badge>
- **Impersonation logs** — permanent audit trail for all super admin impersonation sessions. <larecipe-badge type="success">New</larecipe-badge>

### Developer

- **LaRecipe documentation** — built-in developer documentation at `/documentation`. <larecipe-badge type="success">New</larecipe-badge>

### Stack

<larecipe-badge type="primary">Laravel 12</larecipe-badge> <larecipe-badge type="primary">Filament 5</larecipe-badge> <larecipe-badge type="primary">Livewire 3</larecipe-badge> <larecipe-badge type="primary">Alpine.js</larecipe-badge> <larecipe-badge type="primary">Tailwind CSS</larecipe-badge> <larecipe-badge type="info">PHP 8.2+</larecipe-badge> <larecipe-badge type="info">MySQL 8+</larecipe-badge>
