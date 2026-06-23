# My Bookings

---

- [Overview](#section-overview)
- [Booking Form Pre-fill](#section-prefill)
- [Cancelling a Booking](#section-cancel)
- [Business Settings](#section-settings)

<a name="section-overview"></a>
## Overview

<larecipe-badge type="success" rounded>Requires Login</larecipe-badge>
<larecipe-badge type="primary" rounded>yourdomain.com/my-bookings</larecipe-badge>

The **My Bookings** page gives every registered client a single place to track their appointments across **all businesses** on your platform — not just one.

<img src="/docs/screenshots/my-bookings.png" alt="My Bookings — upcoming appointments grouped by business, collapsible past & cancelled section below" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

The page is split into two sections:

| Section | Contents |
|---|---|
| **Upcoming** | All `pending` and `confirmed` bookings on or after today, ordered by date, grouped by business |
| **Past & Cancelled** | Most recent 20 past or cancelled bookings in a collapsible toggle |

Each booking card shows:

- **Date badge** — month, day of month, and weekday, coloured in the business's brand colour
- **Service name** and time range
- **Status pill** — Confirmed (green), Pending (amber), Cancelled (grey), Completed (blue), No-Show (red)
- **Cancel button** — visible only for upcoming, cancellable bookings
- **Cancellation reason** — shown in italic on cancelled cards if a reason was provided

---

<a name="section-prefill"></a>
## Booking Form Pre-fill

When a logged-in client visits any public booking page, their details are automatically populated on the **Your Details** step:

| Field | Source |
|---|---|
| Full Name | From your account profile |
| Email | From your account (locked — cannot be changed) |
| Phone | From your account profile |

The **email field is read-only** for logged-in users. This guarantees that every booking is stored under the correct account email and will appear in My Bookings.

> {primary.fa-info-circle} The email lock prevents the most common support issue: a client types a typo or a different address on the booking form and then asks "where is my booking?"

An identity banner at the top of the Your Details step confirms who is booking:

<div style="background:#f5f3ff;border:1px solid #e0d9ff;border-radius:0.5rem;padding:0.75rem 1rem;margin:1rem 0;display:inline-flex;align-items:center;gap:0.6rem">
  <span style="font-size:0.9rem">👤</span>
  <div>
    <div style="font-size:0.8rem;font-weight:700;color:#5b21b6">Demo Client</div>
    <div style="font-size:0.75rem;color:#7c3aed">client@example.com</div>
  </div>
</div>

---

<a name="section-cancel"></a>
## Cancelling a Booking

Clients can cancel any upcoming, cancellable booking directly from My Bookings. Clicking **Cancel** opens a modal:

<img src="/docs/screenshots/my-bookings-cancel-modal.png" alt="Cancel booking modal — booking details subtitle, optional reason textarea, Yes cancel booking and Keep it buttons" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

**What happens when the client confirms:**

1. Booking status → **Cancelled**
2. Cancellation reason saved (if entered)
3. A **cancellation confirmation email** is sent to the client
4. Staff receive an in-app notification: _"Booking #N cancelled by {name} — 'reason'"_
5. The slot is freed immediately for new bookings

The booking card updates in-place — no page reload. The status badge changes to "Cancelled" and the Cancel button disappears.

---

<a name="section-settings"></a>
## Business Settings

Each business controls whether clients can cancel online:

<larecipe-badge type="primary" rounded>Business Panel → Settings → Booking Behaviour</larecipe-badge>

| Setting | Default | Effect |
|---|---|---|
| **Allow clients to cancel bookings online** | On | Clients see the Cancel button and can cancel from My Bookings or via email link |
| | Off | Cancel button is hidden; clients see _"This business does not allow online cancellations. Please contact them directly."_ |

> {warning.fa-exclamation-triangle} Disabling online cancellation does not prevent staff from cancelling bookings inside the Business Panel — it only affects the client-facing flow.
