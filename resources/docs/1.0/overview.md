# Introduction

---

- [What is Slotara?](#section-what)
- [Feature Highlights](#section-features)
- [Tech Stack](#section-stack)
- [Panels at a Glance](#section-panels)

<a name="section-what"></a>
## What is Slotara?

<larecipe-badge type="success" rounded>v1.0</larecipe-badge>
<larecipe-badge type="info" rounded>Laravel 12</larecipe-badge>
<larecipe-badge type="primary" rounded>Filament 5</larecipe-badge>
<larecipe-badge type="warning" rounded>Multi-Tenant SaaS</larecipe-badge>

**Slotara** is a self-hosted, multi-tenant booking SaaS platform. Deploy it once — every business (tenant) gets their own branded public booking page where clients can browse services, pick a provider, choose a time slot, and pay, all without leaving the page.

Tenant owners manage their business through a dedicated **Business Panel**. You control the entire platform from the **Super Admin Panel**.

<img src="/docs/screenshots/booking-page-desktop.png" alt="Slotara public booking page — desktop view showing service selection" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

---

<a name="section-features"></a>
## Feature Highlights

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:1rem;margin:1.5rem 0">

  <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:0.75rem;padding:1.25rem 1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05)">
    <div style="font-size:1.6rem;margin-bottom:0.5rem">📅</div>
    <div style="font-size:0.95rem;font-weight:700;color:#1e293b;margin-bottom:0.4rem">Smart Booking Engine</div>
    <div style="font-size:0.875rem;color:#64748b;line-height:1.65">Real-time slot generation based on provider working hours, service duration, and existing bookings. No double-booking is ever possible.</div>
  </div>

  <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:0.75rem;padding:1.25rem 1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05)">
    <div style="font-size:1.6rem;margin-bottom:0.5rem">🏢</div>
    <div style="font-size:0.95rem;font-weight:700;color:#1e293b;margin-bottom:0.4rem">Full Multi-Tenancy</div>
    <div style="font-size:0.875rem;color:#64748b;line-height:1.65">Each business is completely isolated — their own services, providers, staff, bookings, clients, and settings share the same database but are never visible to each other.</div>
  </div>

  <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:0.75rem;padding:1.25rem 1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05)">
    <div style="font-size:1.6rem;margin-bottom:0.5rem">💳</div>
    <div style="font-size:0.95rem;font-weight:700;color:#1e293b;margin-bottom:0.4rem">Stripe Billing</div>
    <div style="font-size:0.875rem;color:#64748b;line-height:1.65">Subscription billing for your tenants. Businesses pay for their plan via Stripe Checkout. Webhooks auto-provision tenants on payment success and handle renewals and cancellations.</div>
  </div>

  <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:0.75rem;padding:1.25rem 1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05)">
    <div style="font-size:1.6rem;margin-bottom:0.5rem">📧</div>
    <div style="font-size:0.95rem;font-weight:700;color:#1e293b;margin-bottom:0.4rem">Email Engine</div>
    <div style="font-size:0.875rem;color:#64748b;line-height:1.65">Fully customisable transactional email templates (booking confirmation, cancellation, reminders). Queue-powered delivery via any SMTP provider — Mailgun, Postmark, Amazon SES, or your own.</div>
  </div>

  <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:0.75rem;padding:1.25rem 1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05)">
    <div style="font-size:1.6rem;margin-bottom:0.5rem">📖</div>
    <div style="font-size:0.95rem;font-weight:700;color:#1e293b;margin-bottom:0.4rem">My Bookings Portal</div>
    <div style="font-size:0.875rem;color:#64748b;line-height:1.65">Logged-in clients get a cross-business <code>/my-bookings</code> page — upcoming and past appointments from every business, grouped by brand, with one-click cancellation (with reason).</div>
  </div>

  <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:0.75rem;padding:1.25rem 1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05)">
    <div style="font-size:1.6rem;margin-bottom:0.5rem">🗂️</div>
    <div style="font-size:0.95rem;font-weight:700;color:#1e293b;margin-bottom:0.4rem">Form Builder</div>
    <div style="font-size:0.875rem;color:#64748b;line-height:1.65">8-tab control centre for your public booking page: business profile, services, providers, drag-and-drop intake form (10 field types including signatures &amp; file uploads), visual theme, success screen, and embed code or QR code — all in one place.</div>
  </div>

  <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:0.75rem;padding:1.25rem 1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05)">
    <div style="font-size:1.6rem;margin-bottom:0.5rem">🔒</div>
    <div style="font-size:0.95rem;font-weight:700;color:#1e293b;margin-bottom:0.4rem">Security Suite</div>
    <div style="font-size:0.875rem;color:#64748b;line-height:1.65">reCAPTCHA v3 on forms, password policy enforcement (complexity + history + expiry), login rate limiting, session timeout, and account lockouts.</div>
  </div>

  <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:0.75rem;padding:1.25rem 1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05)">
    <div style="font-size:1.6rem;margin-bottom:0.5rem">📬</div>
    <div style="font-size:0.95rem;font-weight:700;color:#1e293b;margin-bottom:0.4rem">Contact Inbox</div>
    <div style="font-size:0.875rem;color:#64748b;line-height:1.65">Contact form with reCAPTCHA protection feeds an in-app inbox. Admin can read and reply to each message directly from the panel.</div>
  </div>

  <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:0.75rem;padding:1.25rem 1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05)">
    <div style="font-size:1.6rem;margin-bottom:0.5rem">🌐</div>
    <div style="font-size:0.95rem;font-weight:700;color:#1e293b;margin-bottom:0.4rem">Multi-Language UI</div>
    <div style="font-size:0.875rem;color:#64748b;line-height:1.65">Complete UI translation in 8 languages: English, Spanish, German, French, Arabic (RTL), Russian, Chinese, and Hindi. Users switch language instantly from the header — no reload required.</div>
  </div>

</div>

---

<a name="section-stack"></a>
## Tech Stack

| Layer | Technology |
|---|---|
| Backend Framework | Laravel 12 |
| Admin & Business UI | Filament 5 |
| Reactive Components | Livewire 3 + Alpine.js |
| Styling | Tailwind CSS |
| Database | MySQL 8+ / MariaDB 10.6+ |
| Cache & Queue | Database driver (Redis optional) |
| Roles & Permissions | Spatie Laravel Permission |
| Media Handling | Spatie Media Library |
| Payments | Stripe Checkout |

---

<a name="section-panels"></a>
## Panels at a Glance

| Panel | URL | Who Uses It |
|---|---|---|
| Super Admin Panel | `/admin` | Platform owner — manages tenants, settings, users, plans |
| Business Panel | `/manage` | Tenant owner & staff — services, bookings, providers, clients |
| Public Booking Page | `/{slug}` | End clients — browse services and book appointments |
| My Bookings | `/my-bookings` | Logged-in clients — view & cancel bookings across all businesses |

> {primary.fa-rocket} **Getting started?** Follow the path: [Requirements](/{{route}}/{{version}}/requirements) → [Installation](/{{route}}/{{version}}/installation) → [Configuration](/{{route}}/{{version}}/configuration)
