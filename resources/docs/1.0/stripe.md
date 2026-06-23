# Stripe Payments

---

- [Overview](#section-overview)
- [Credentials Setup](#section-setup)
- [How Subscription Billing Works](#section-flow)
- [Webhook Configuration](#section-webhooks)
- [Testing Payments](#section-testing)
- [Troubleshooting](#section-troubleshoot)

<a name="section-overview"></a>
## Overview

Slotara uses **Stripe Checkout** to collect subscription payments from your tenants (the businesses that sign up on your platform). When a new business registers on a paid plan, they are redirected to a Stripe-hosted checkout page to enter their card details.

> {success.fa-shield} Stripe Checkout is fully hosted by Stripe — PCI compliance, 3D Secure, and error handling are included. You never touch raw card data.

Stripe handles:
- New tenant subscription sign-up (checkout redirect at registration)
- Subscription renewal (automatic recurring billing)
- Subscription cancellation and plan changes
- Sending invoices and payment receipts to tenants

---

<a name="section-setup"></a>
## Credentials Setup

<larecipe-badge type="primary" rounded>Admin → Settings → Payments</larecipe-badge>

Enter your Stripe keys in the Admin Settings panel and your `.env` file:

| Field | Where to Find It | Example |
|---|---|---|
| **Stripe Publishable Key** | Stripe Dashboard → API Keys | `pk_live_...` |
| **Stripe Secret Key** | Stripe Dashboard → API Keys | `sk_live_...` |
| **Webhook Signing Secret** | Stripe Dashboard → Webhooks → Signing Secret | `whsec_...` |

```env
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

> {danger.fa-ban} Keep your **Secret Key** (`sk_live_...`) private. Never commit it to version control or expose it in client-side code.

---

<a name="section-flow"></a>
## How Subscription Billing Works

When a business registers on a **paid plan**:

```
Business owner fills in registration form
          ↓
Selects a paid subscription plan
          ↓
Redirected to Stripe Checkout (hosted payment page)
          ↓
         / \
   Payment   Payment
   Succeeds  Fails / Abandoned
       ↓           ↓
  Stripe sends   Registration
  webhook event  not completed
       ↓
  Tenant automatically provisioned
  Welcome email sent to owner
  Business Panel accessible
```

For subscription renewals, Stripe handles billing automatically on the renewal date. If a payment fails, Stripe retries according to your Stripe Dashboard dunning settings.

When a subscription is cancelled or expires:
- The tenant's booking page is suspended automatically
- Clients visiting the booking page see an unavailable message
- The business owner receives a notification to resubscribe

### Creating Paid Plans

Before tenants can subscribe, you need to create the products and prices in Stripe first, then link them in Slotara:

1. Log in to **Stripe Dashboard → Products**
2. Click **+ Add Product** and set a name (e.g. "Slotara Pro")
3. Add a recurring price — set the amount, currency, and billing interval (monthly or annual)
4. Copy the **Price ID** (starts with `price_`)
5. Go to **Admin → Subscription Plans** in Slotara
6. Create or edit a plan and paste the **Price ID** into the Stripe Price ID field

---

<a name="section-webhooks"></a>
## Webhook Configuration

Slotara listens for Stripe events at:

```
https://yourdomain.com/stripe/webhook
```

**Step 1 — Register the webhook in Stripe:**

1. Go to **Stripe Dashboard → Developers → Webhooks**
2. Click **+ Add endpoint**
3. Enter your endpoint URL: `https://yourdomain.com/stripe/webhook`
4. Under **Events to send**, add:
   - `checkout.session.completed`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
5. Click **Add endpoint**

**Step 2 — Copy the signing secret:**

1. Click on your newly created webhook
2. Under **Signing secret**, click **Reveal**
3. Copy the `whsec_...` value
4. Paste it into **Admin → Settings → Payments → Webhook Secret** and your `.env`

> {danger.fa-ban} Without a valid Webhook Secret, Slotara cannot verify that events genuinely came from Stripe. Tenant provisioning after payment will not work.

---

<a name="section-testing"></a>
## Testing Payments

Use Stripe **test mode** keys (`pk_test_...` / `sk_test_...`) during development. Test transactions don't charge real cards.

Test card numbers:

| Card Number | Scenario |
|---|---|
| `4242 4242 4242 4242` | Payment succeeds immediately |
| `4000 0000 0000 9995` | Card declined |
| `4000 0025 0000 3155` | 3D Secure authentication required |

Expiry: any future date (e.g. `12/34`) · CVC: any 3 digits · ZIP: any 5 digits

For **local webhook testing**, use the Stripe CLI:

```bash
# Install Stripe CLI, then:
stripe listen --forward-to http://localhost:8000/stripe/webhook
```

The CLI prints a local signing secret (`whsec_test_...`) — use this in your local `.env`.

---

<a name="section-troubleshoot"></a>
## Troubleshooting

| Symptom | Likely Cause | Fix |
|---|---|---|
| Tenant not provisioned after payment | Webhook not reaching server | Check Stripe Dashboard → Webhooks → recent deliveries |
| Webhook signature verification failed | Wrong `STRIPE_WEBHOOK_SECRET` | Re-copy the signing secret from Stripe and update `.env` |
| Webhook fails with 419 (CSRF) | Webhook route not CSRF-excluded | The `/stripe/webhook` route is excluded from CSRF in `bootstrap/app.php` — confirm it is present |
| Redirect back from Stripe shows error | `APP_URL` mismatch | Ensure `APP_URL` in `.env` matches your actual domain |
| Plan's Stripe Price ID missing | Price not linked | Edit the plan in Admin → Subscription Plans and paste the `price_...` ID from Stripe |
