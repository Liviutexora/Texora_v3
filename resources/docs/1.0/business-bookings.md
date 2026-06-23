# Bookings

---

- [Booking Statuses](#section-statuses)
- [Managing Bookings](#section-manage)
- [Creating Bookings Manually](#section-manual)
- [Client Cancellations](#section-cancel)
- [My Bookings Portal](#section-my-bookings)
- [iCal Export](#section-ical)

<a name="section-statuses"></a>
## Booking Statuses

Each booking moves through a lifecycle from creation to completion:

| Status | Badge | When It's Set |
|---|---|---|
| **Pending** | <larecipe-badge type="warning">Pending</larecipe-badge> | Created — awaiting confirmation or payment |
| **Confirmed** | <larecipe-badge type="primary">Confirmed</larecipe-badge> | Manually confirmed, or payment received via Stripe |
| **Cancelled** | <larecipe-badge type="danger">Cancelled</larecipe-badge> | Cancelled by the client (via link) or by staff |
| **Completed** | <larecipe-badge type="success">Completed</larecipe-badge> | Service was delivered — set manually by staff |
| **No-Show** | <larecipe-badge type="danger">No-Show</larecipe-badge> | Client did not appear — set manually by staff |

```
Client books
      ↓
   Pending
      ↓
Confirmed (free: immediate / paid: after Stripe webhook)
      ↓
  Completed ──── or ──── No-Show
      ↑
  Cancelled (at any point by client or staff)
```

> {primary.fa-info-circle} The sidebar badge shows today's **Pending + Confirmed** count only. Completed, Cancelled, and No-Show bookings are excluded.

---

<a name="section-manage"></a>
## Managing Bookings

<larecipe-badge type="primary" rounded>Business Panel → Bookings</larecipe-badge>

<img src="/docs/screenshots/business-bookings-list.png" alt="Bookings list with status badge filters, date range picker, search bar, and per-row action menu" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

**Filtering the list:**

| Filter | Options |
|---|---|
| **Status** | Pending, Confirmed, Cancelled, Completed, No-Show |
| **Service** | Dropdown of your services |
| **Provider** | Dropdown of your providers |
| **Date Range** | From / To date pickers |
| **Search** | Client name or email |

**Per-booking actions:**

| Action | What It Does |
|---|---|
| **View** | Open booking details — client info, service, provider, notes |
| **Edit** | Change status or add internal notes |
| **Cancel** | Set status to Cancelled |
| **Export** | Download the filtered list as Excel or CSV |

> {warning.fa-exclamation-triangle} Cancelling from the panel does **not** automatically email the client. Cancellation emails are only sent when the client uses their own cancellation link.

---

<a name="section-manual"></a>
## Creating Bookings Manually

Staff and owners can create bookings on behalf of clients — useful for phone or walk-in reservations.

1. Click **New Booking** in the top-right of the Bookings list
2. Select the **Service**
3. Select the **Provider**
4. Pick an available **Date** and **Time** from the slot picker
5. Enter the client's **Name**, **Email**, and **Phone**
6. Add optional **Notes**
7. Click **Save**

**What happens automatically:**

- The slot is reserved immediately
- A **confirmation email** is sent to the client
- The booking appears in the list with **Confirmed** status

> {primary.fa-lightbulb-o} Manual bookings bypass the payment step. Even paid services are confirmed directly — collect payment separately if needed.

<img src="/docs/screenshots/business-bookings-list.png" alt="New Booking — business panel bookings management with filters, search, and per-row actions" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

---

<a name="section-cancel"></a>
## Client Cancellations

Clients can cancel their own bookings in two ways: via the **cancellation link** in their confirmation email, or directly from the **My Bookings** portal.

### Cancellation Link (email)

Every booking confirmation email includes a unique cancellation link tied to that specific booking. Clicking it opens a self-service page where the client confirms and the slot is freed immediately.

### My Bookings Portal (in-app)

Logged-in clients can cancel from `/my-bookings` without leaving the app. A modal appears asking for an optional **reason for cancellation**:

<img src="/docs/screenshots/my-bookings-cancel-modal.png" alt="Cancel booking modal with optional reason textarea — Yes cancel booking and Keep it buttons" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

The cancellation reason is stored on the booking and visible in **Business Panel → Bookings → View**. It also appears in italic on the cancelled card in the client's My Bookings page.

### Controlling cancellation permissions

You can disable self-service cancellations for your business entirely:

<larecipe-badge type="primary" rounded>Business Panel → Settings → Booking Behaviour</larecipe-badge>

Toggle **"Allow clients to cancel bookings online"** off. Clients will see a message directing them to contact you directly instead.

**What happens on cancellation (either method):**

1. Booking status → **Cancelled**
2. Cancellation reason stored (if provided)
3. A **cancellation email** is sent to the client
4. Staff receive an in-app notification: _"Booking #N cancelled by {name}"_
5. The time slot becomes available immediately for new bookings

> {success.fa-check-circle} Client-initiated cancellations are fully self-service — no staff involvement needed. The slot reopens automatically.

---

<a name="section-ical"></a>
## iCal Export

Every booking generates a standard iCal (`.ics`) file that clients can add to any calendar app.

**Download URL:**

```
https://yourdomain.com/booking/{token}/ical
```

The link is included in the booking confirmation email. It works with:

| Calendar App | Compatible |
|---|---|
| Google Calendar | ✅ |
| Apple Calendar (iOS / macOS) | ✅ |
| Microsoft Outlook | ✅ |
| Any iCal-compatible app | ✅ |

> The iCal event includes the service name, provider, date, time, and duration. It does not auto-update if the booking is changed — clients must download a new `.ics` if the booking is rescheduled.

---

<a name="section-my-bookings"></a>
## My Bookings Portal

Logged-in clients get a single dashboard at `/my-bookings` that shows all their appointments **across every business** they've booked with — not just yours.

<img src="/docs/screenshots/my-bookings.png" alt="My Bookings page — upcoming appointments grouped by business, with collapsible past and cancelled section" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

**What clients see:**

| Section | Contents |
|---|---|
| **Upcoming** | All pending and confirmed bookings, ordered by date, grouped by business |
| **Past & Cancelled** | Last 20 past or cancelled bookings in a collapsible section |

Each booking card shows the service name, date badge (month/day/weekday in the business brand colour), time range, and status pill. Cancelled cards show the reason in italic if one was provided.

**Booking form pre-fill**

When a logged-in client visits any booking page, their **Full Name**, **Email**, and **Phone** are automatically pre-filled from their account. The email field is locked — it can't be changed — so every booking is guaranteed to appear under their account.

> {primary.fa-info-circle} The email lock prevents the common issue of bookings going "missing" because a client accidentally typed a different address on the booking form.
