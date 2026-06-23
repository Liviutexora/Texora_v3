# Subscription Plans

---

- [Plan Fields](#section-fields)
- [Creating a Free Plan](#section-free)
- [Creating a Paid Plan](#section-paid)
- [Changing a Tenant's Plan](#section-change)

<a name="section-fields"></a>
## Plan Fields

| Field | Description |
|---|---|
| **Name** | Shown to registrants on the sign-up page (e.g. "Starter", "Pro", "Agency") |
| **Price** | Monthly price. Set to `0` for a free plan |
| **Stripe Price ID** | The `price_...` ID from Stripe Dashboard (required for paid plans) |
| **Features** | Bullet-point list displayed on the registration pricing page |
| **Is Active** | Only active plans are shown during registration |
| **Sort Order** | Drag to reorder plans on the registration page |

<img src="/docs/screenshots/admin-subscription-plans-list.png" alt="Admin — Subscription Plans list showing plan names, prices, and Stripe IDs" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

---

<a name="section-free"></a>
## Creating a Free Plan

1. Go to **Admin → Subscription Plans → New Plan**
2. Enter a **Name** (e.g. "Free")
3. Set **Price** to `0.00`
4. Leave **Stripe Price ID** empty
5. Add any feature bullets you want shown (e.g. "Up to 3 services", "1 provider")
6. Toggle **Is Active** on
7. Save

Tenants on a free plan are provisioned **immediately** at registration — no payment step.

> {primary.fa-lightbulb-o} A free plan lets businesses try Slotara before upgrading. You can convert a free-plan tenant to paid at any time from the tenant edit screen.

---

<a name="section-paid"></a>
## Creating a Paid Plan

**Step 1 — Create the product in Stripe:**

1. Log in to [Stripe Dashboard](https://dashboard.stripe.com) → **Products**
2. Click **+ Add Product**
3. Enter a product name (e.g. "Slotara Pro")
4. Add a **recurring price**: set the amount, currency, and billing interval (monthly/annual)
5. Save the product
6. Copy the **Price ID** — it starts with `price_`

<img src="/docs/screenshots/admin-subscription-plan-edit.png" alt="Admin — Subscription Plan edit showing name, price, Stripe Price ID, and feature bullets" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

**Step 2 — Create the plan in Slotara:**

1. Go to **Admin → Subscription Plans → New Plan**
2. Enter the **Name** and **Price** (must match Stripe)
3. Paste the **Price ID** into the **Stripe Price ID** field
4. Add feature bullets
5. Toggle **Is Active** on
6. Save

**Registration flow for paid plans:**

```
User fills registration form
        ↓
Redirected to Stripe Checkout (secure payment page)
        ↓
Payment succeeds
        ↓
Business account created automatically
        ↓
Welcome email sent
```

> {warning.fa-exclamation-triangle} Make sure your **Stripe Webhook** is configured and your **Signing Secret** is saved in Admin → Settings → Payments. Without it, the webhook won't be verified and tenants will not be provisioned after payment.

---

<a name="section-change"></a>
## Changing a Tenant's Plan

To move a tenant from one plan to another:

1. Go to **Admin → Businesses**
2. Click **Edit** on the tenant
3. Change the **Subscription Plan** dropdown
4. Save

> {primary.fa-info-circle} Plan changes take effect immediately. There is no automatic Stripe prorating — manage billing adjustments directly in your Stripe Dashboard.
