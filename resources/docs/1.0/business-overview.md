# Business Panel Overview

---

- [Accessing the Panel](#section-access)
- [Panel Sections](#section-sections)
- [Navigation Badges](#section-badges)
- [Public Booking Page](#section-public)

<a name="section-access"></a>
## Accessing the Panel

<larecipe-badge type="primary" rounded>yourdomain.com/manage</larecipe-badge>

The Business Panel is the day-to-day workspace for every tenant. Owners, providers, and staff each see a view tailored to their role.

**Required roles to access:**

| Role | Badge | Can Access |
|---|---|---|
| <larecipe-badge type="primary">tenant_owner</larecipe-badge> | Owner | Full panel — services, providers, staff, bookings, settings |
| <larecipe-badge type="info">provider</larecipe-badge> | Provider | Bookings only (their own) |
| <larecipe-badge type="warning">staff</larecipe-badge> | Staff | Bookings only (all) |

<img src="/docs/screenshots/business-dashboard.png" alt="Business Panel dashboard showing booking stats, sidebar navigation, and quick-action buttons" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

---

<a name="section-sections"></a>
## Panel Sections

| Section | Who Can Access | Purpose |
|---|---|---|
| **Dashboard** | All roles | Today's stats — confirmed, pending, cancelled bookings |
| **Bookings** | All roles | View, filter, create, and manage all bookings |
| **Services** | Owner only | Create and configure bookable services |
| **Providers** | Owner only | Manage provider profiles and working hours |
| **Staff** | Owner only | Manage staff accounts and access |
| **Clients** | Owner only | Client history, lifetime spend, top services |
| **Notification Preferences** | All roles | Per-user email and in-app notification opt-ins |
| **Profile** | All roles | Account details and password |

> {primary.fa-info-circle} **Role-based visibility** — Staff and Providers see a simplified sidebar. Sections like Services, Clients, and Settings are hidden entirely from their view — not read-only, just absent.

---

<a name="section-badges"></a>
## Navigation Badges

The sidebar shows live counts so staff always know what needs attention:

| Nav Item | Badge Shows | Colour |
|---|---|---|
| **Bookings** | Today's pending + confirmed bookings count | <larecipe-badge type="warning">Yellow</larecipe-badge> |
| **Clients** | Total distinct clients (unique by email) | <larecipe-badge type="primary">Blue</larecipe-badge> |

> {success.fa-check-circle} Badges update in real time as bookings are created or status changes. No page refresh needed.

---

<a name="section-public"></a>
## Public Booking Page

Every tenant gets a unique public URL for their customers:

```
https://yourdomain.com/{slug}
```

The `{slug}` is set when the business is created and must be unique across the platform.

**What the booking page shows:**

```
Customer visits /b/my-salon
        ↓
Browses active services (name, price, duration, image)
        ↓
Selects a service → chooses a provider
        ↓
Picks an available date and time slot
        ↓
Fills in name, email, phone, optional notes
        ↓
Free service → booking confirmed immediately
Paid service → redirected to Stripe Checkout
        ↓
Confirmation email sent with cancellation link
```

> {warning.fa-exclamation-triangle} The booking page only works when the business has at least one **active service** with an **active provider** assigned. An empty business shows "not available" to customers.

<img src="/docs/screenshots/booking-page-lumina.png" alt="Public booking page showing service cards, provider selection, and calendar slot picker" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">
