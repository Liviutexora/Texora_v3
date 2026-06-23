# Providers & Staff

---

- [Providers Overview](#section-providers)
- [Provider Fields](#section-provider-fields)
- [Working Hours](#section-hours)
- [Staff Overview](#section-staff)
- [Adding Staff](#section-add-staff)
- [Role Comparison](#section-comparison)

<a name="section-providers"></a>
## Providers Overview

<larecipe-badge type="primary" rounded>Business Panel → Providers</larecipe-badge>

Providers are the **bookable team members** — the people clients actually schedule time with. Each provider has their own profile, schedule, and service assignments.

<img src="/docs/screenshots/business-providers-list.png" alt="Business Panel — Providers list showing provider names, job titles, experience, and services they deliver" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

---

<a name="section-provider-fields"></a>
## Provider Fields

| Field | Description |
|---|---|
| **Name** | Displayed on the booking page provider card |
| **Email** | Links this provider to a user account on the platform |
| **Bio** | Short description shown on the booking page |
| **Avatar** | Profile photo — shown on the booking page and panel |
| **Working Hours** | Day-by-day schedule defining bookable windows |
| **Is Active** | Only active providers appear on the booking page |

> {primary.fa-info-circle} The **Email** field links the provider to a user account. If no account exists for that email, one is created automatically and a welcome email is sent.

---

<a name="section-hours"></a>
## Working Hours

Working hours define when a provider is available for bookings. Set per day of the week.

**To configure:**

1. Edit the provider
2. Open the **Working Hours** tab
3. Toggle each day on or off
4. Set **Start Time** and **End Time** for enabled days
5. Save

```
Monday     ✅  09:00 – 17:00
Tuesday    ✅  09:00 – 17:00
Wednesday  ✅  09:00 – 13:00   ← half day
Thursday   ✅  09:00 – 17:00
Friday     ✅  09:00 – 17:00
Saturday   ❌  (off)
Sunday     ❌  (off)
```

<img src="/docs/screenshots/business-provider-edit.png" alt="Provider edit form — job title, years of experience, bio, and service assignment" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

> {warning.fa-exclamation-triangle} A provider with **no working hours configured** (all days off) will have zero available slots, regardless of the service settings.

---

<a name="section-staff"></a>
## Staff Overview

<larecipe-badge type="primary" rounded>Business Panel → Staff</larecipe-badge>

Staff are team members who need access to the Business Panel to help manage bookings — but are **not themselves bookable**. They don't appear on the public booking page.

**What staff can do:**

| Action | Allowed |
|---|---|
| View all bookings | ✅ Yes |
| Create bookings manually | ✅ Yes |
| Change booking status | ✅ Yes |
| View services | ✅ Read-only |
| Edit services | ❌ No |
| View providers | ✅ Read-only |
| Edit providers | ❌ No |
| View clients | ❌ No |
| Change business settings | ❌ No |

> {danger.fa-ban} Staff cannot access the **Clients** section or **Business Settings**. These are owner-only areas. If a staff member needs these permissions, they should be given the `tenant_owner` role instead.

---

<a name="section-add-staff"></a>
## Adding Staff

1. Go to **Business Panel → Staff → Add Staff**
2. Enter their **email address**
3. If an account with that email already exists — it is linked
4. If no account exists — a new user account is created automatically
5. The new staff member receives a **welcome email** with login instructions

> {primary.fa-info-circle} Staff log in at `/manage` — not `/admin`. Removing a staff member from your team does not delete their user account.

---

<a name="section-comparison"></a>
## Role Comparison

| Capability | <larecipe-badge type="primary">Owner</larecipe-badge> | <larecipe-badge type="info">Provider</larecipe-badge> | <larecipe-badge type="warning">Staff</larecipe-badge> |
|---|---|---|---|
| Manage services | ✅ | ❌ | ❌ |
| Manage providers | ✅ | ❌ | ❌ |
| Manage staff | ✅ | ❌ | ❌ |
| View all bookings | ✅ | ❌ | ✅ |
| View own bookings | ✅ | ✅ | ✅ |
| Create bookings | ✅ | ❌ | ✅ |
| View clients | ✅ | ❌ | ❌ |
| Business settings | ✅ | ❌ | ❌ |
| Appears on booking page | ✅ (if provider too) | ✅ | ❌ |
