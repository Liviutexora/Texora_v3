# Slotara — Multi-Tenant Booking SaaS

> Accept appointments online for any service-based business — salons, gyms, clinics, tutors, and more.

Built on **Laravel 12 · Filament 5 · Livewire 3 · TailwindCSS 4**.  
Sold via [CodeCanyon PHP Scripts](https://codecanyon.net).

---

## Features at a Glance

### For your customers (tenants)
| | |
|---|---|
| 🧑‍💻 **Public booking wizard** | 5-step Livewire wizard — service → provider → date/slot → details → confirmation |
| 🎨 **White-label branding** | Logo, tagline, brand colour per tenant |
| 📅 **iCal download** | One-click `.ics` calendar invite on confirmation |
| 🔗 **My Bookings portal** | Authenticated clients see all their appointments across all businesses |
| ❌ **Self-service cancellation** | Cancel from My Bookings or via one-time signed URL with optional reason |
| 📋 **Custom form fields** | Tenant adds extra questions (text, dropdown, textarea) |
| 🔐 **Social login** | Clients sign in with Google or GitHub (OAuth via Laravel Socialite) |

### Tenant admin panel (`/manage`)
| | |
|---|---|
| 📊 **Dashboard** | Today's bookings, monthly revenue, trend charts |
| 📆 **Bookings** | Confirm · complete · cancel · reschedule · bulk CSV export |
| 🧑 **Providers** | Manage staff, shifts, services, blocked dates |
| 🛍️ **Services** | Drag-to-reorder CRUD with colour coding |
| 📈 **Analytics** | Conversion rates, top providers, revenue by service |
| ⚙️ **Settings** | Branding, timezone, currency, custom fields, booking behaviour |
| 👥 **Staff roles** | Invite staff with scoped permissions |

### Super admin (`/admin`)
| | |
|---|---|
| 🏗️ **Subscription Plans** | Create plans with provider/booking limits |
| 🏢 **Tenant Management** | Activate, suspend, impersonate, change plan |
| 📋 **Audit Log** | Immutable record of every impersonation session |
| 📤 **GDPR Export** | Download all client data for a tenant as JSON |
| 📊 **Platform Stats** | MRR, active tenants, daily booking counts |
| 📧 **Email Templates** | Customise all transactional emails with live preview |
| 🔒 **Security settings** | IP whitelisting, 2FA enforcement, password policy, login activity |
| 💾 **Database backup** | One-click backup from admin panel |

### Technical
- **Multi-tenancy** — manual path-based (no external package), `BelongsToTenant` trait
- **Plan limits** — `max_providers` and `max_bookings_per_month` enforced at runtime
- **Email queue** — confirmation (+ ICS attachment), reminder, cancellation, provider alert
- **Two-factor authentication** — TOTP-based 2FA for admin and tenant owners
- **Notification preferences** — users control which emails they receive
- **REST API** — Sanctum-authenticated JSON API at `/api/v1/` (auth + file uploads; booking endpoints on roadmap)
- **Installer** — web wizard at `/install`; `.installed` gate after first run
- **Demo seeder** — 8 ready-made businesses across 8 verticals for CodeCanyon preview

---

## Requirements

- PHP 8.2+ with `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `gd`, `zip`, `intl`
- MySQL 8.0+
- Composer 2.x · Node 18+

---

## Quick Start

See **[INSTALL.md](INSTALL.md)** for full step-by-step instructions.

```bash
# 1 – install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 2 – copy and configure env
cp .env.example .env && php artisan key:generate

# 3 – open the web installer
open http://localhost/install
```

---

## Demo Credentials (after seeding)

| Role | Email | Password | Panel |
|------|-------|----------|-------|
| Super Admin | `admin@slotara.app` | `password` | `/admin` |
| Salon owner | `owner@velvet-chair.demo` | `password` | `/manage` |
| Gym owner | `owner@ironedge-fitness.demo` | `password` | `/manage` |
| Photo studio owner | `owner@lenslife-studio.demo` | `password` | `/manage` |
| Staff (Salon) | `staff@slotara.app` | `password` | `/manage` |
| Client | `client@slotara.app` | `password` | `/my-bookings` |

Demo booking pages (8 businesses seeded):

| Business | Type | URL |
|----------|------|-----|
| Velvet Chair Studio | Salon | `/velvet-chair` |
| ClearPath Clinic | Medical | `/clearpath-clinic` |
| Apex Advisory | Consulting | `/apex-advisory` |
| IronEdge Fitness | Gym | `/ironedge-fitness` |
| BrightMind Tutoring | Education | `/brightmind-tutoring` |
| Pixora Creative | Agency | `/pixora-creative` |
| RevUp Auto | Auto Shop | `/revup-auto` |
| LensLife Studio | Photography | `/lenslife-studio` |

---

## URL Map

| URL | Purpose |
|-----|---------|
| `/install` | Web installer (blocked after first run) |
| `/admin` | Super admin panel |
| `/admin/tenants` | Tenant list + impersonation |
| `/admin/subscription-plans` | Plan CRUD |
| `/manage` | Tenant admin panel |
| `/manage/bookings` | Booking management |
| `/manage/analytics` | Analytics & charts |
| `/manage/settings` | Tenant settings |
| `/{tenant-slug}` | Public booking wizard |
| `/{tenant-slug}/my-bookings` | Client booking lookup |
| `/setup` | New tenant onboarding wizard |
| `/booking/{id}/ical` | iCal download |
| `/booking/cancel/{token}` | Self-service cancellation |

---

## Changelog

See **[CHANGELOG.md](CHANGELOG.md)**.

---

## Licence

Regular Licence: one end product, not for resale.  
Extended Licence: multiple end products / SaaS usage; removes "Powered by" branding (`HIDE_POWERED_BY=true`).
