# Booking Payments

---

Slotara supports online payment collection at the time of booking, as well as offline payment recording by staff. Payment is intentionally separated from Slotara's SaaS subscription billing — each tenant uses their own gateway credentials.

---

## Pay-at-booking flow

```
Client confirms booking
    └─► Booking created (payment_status = pending)
    └─► Amount > 0 AND tenant requires payment?
            YES → redirect to gateway checkout
            NO  → instant confirmation (emails + SMS dispatched)

Payment gateway webhook
    └─► BookingPaymentService::markPaid()
            ├─► payment_status = paid, paid_at = now()
            ├─► BookingNotificationService::dispatchForNewBooking()
            └─► SyncBookingToGoogleCalendar (queued, if enabled)

Client redirected to success page
```

---

## Supported gateways

| Gateway | Best for |
|---------|---------|
| **Stripe** | Global businesses, all currencies |
| **Razorpay** | INR payments, South Asia |
| **PayPal** | Global PayPal users |
| **Paddle** | Digital goods, SaaS-style billing |

---

## Tenant setup

1. Go to **Manage → Integrations → Payments**
2. Enable **Online payments**
3. Optionally enable **Require payment at booking** — when on, confirmation emails/SMS are sent only after payment succeeds
4. Click your preferred **gateway card** (Stripe, Razorpay, PayPal, or Paddle) — only that gateway's API fields are shown
5. Enter the API credentials for the selected gateway
6. Register the webhook URL in your gateway dashboard:

| Gateway | Webhook URL |
|---------|-------------|
| Stripe | `https://yourdomain.com/stripe/webhook` |
| Razorpay | `https://yourdomain.com/razorpay/webhook` |
| PayPal | `https://yourdomain.com/paypal/webhook` |
| Paddle | `https://yourdomain.com/paddle/webhook` |

---

## Offline payments

Staff with booking management access can record payments manually:

1. Open a booking in **Manage → Bookings → View**
2. Click the **Payment** action
3. Select method: Cash, Card (in person), or Bank transfer
4. Enter an optional reference number
5. Save — `payment_status` is set to `paid` and notifications are dispatched if the booking was previously pending

Offline methods (Cash, Card terminal, Bank transfer) can each be toggled on or off in the **Offline Payment Methods** section of the payment settings page.

---

## Print receipts

Every booking has a public receipt URL — no login required:

```
GET /booking/{cancellation_token}/receipt
```

The page shows: business logo, name, and contact info; client name, email, and phone; appointment date/time and provider; service line items with total; payment status badge, gateway, reference number, and paid-at timestamp. A **Print** button is visible on screen but hidden when printing.

From the admin panel: **Bookings → View → Print receipt** opens the receipt in a new tab.

<img src="/docs/screenshots/booking-receipt.png" alt="Professional booking receipt showing company logo, customer info, appointment details, amount, and payment status badge" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

---

## Important: separation from SaaS billing

| | Booking payments | SaaS subscription billing |
|--|--|--|
| Stripe keys | Tenant-configured (`TenantPaymentSettings`) | Platform-configured (`BillingService`) |
| Webhook route | `/stripe/webhook` (metadata `type: booking`) | `/billing/stripe/webhook` |
| Purpose | Client pays for service | Tenant subscribes to Slotara |

These two flows never share credentials or webhook handlers.

---

## Key files

| File | Purpose |
|------|---------|
| `app/Support/TenantPaymentSettings.php` | Settings accessor |
| `app/Services/BookingPaymentService.php` | Core payment logic, `requiresPayment()`, `markPaid()` |
| `app/Services/BookingNotificationService.php` | Dispatches emails/SMS after payment |
| `app/Contracts/BookingPaymentGateway.php` | Gateway interface |
| `app/Services/PaymentGateways/` | Stripe, Razorpay, PayPal, Paddle implementations |
| `app/Http/Controllers/BookingPaymentController.php` | Return/cancel URL handlers |
| `app/Http/Controllers/BookingReceiptController.php` | Receipt page |
| `resources/views/booking/receipt.blade.php` | Receipt view |
| `tests/Feature/BookingPaymentTest.php` | Feature test suite |
| `tests/Feature/OfflinePaymentTest.php` | Offline payment tests |
