# Businesses (Tenants)

---

- [Tenant Fields](#section-details)
- [Managing Tenants](#section-manage)
- [Registration Flow](#section-flow)
- [Suspending a Tenant](#section-suspend)

<a name="section-details"></a>
## Tenant Fields

Each business record stores the following:

| Field | Description |
|---|---|
| **Business Name** | Displayed on the public booking page header |
| **Slug** | URL identifier — the booking page is at `/{slug}` |
| **Owner** | The linked user account (must have `tenant_owner` role) |
| **Subscription Plan** | Which plan this tenant is on |
| **Status** | `Active` or `Suspended` |
| **Currency** | Used for display on the booking page |
| **Timezone** | Business-specific timezone for scheduling |
| **Logo** | Appears on the public booking page and in emails |

> {warning.fa-link} The **slug** must be unique platform-wide. Changing it after launch breaks all existing client bookmarks and shared links.

<img src="/docs/screenshots/admin-tenants.png" alt="Admin — Businesses list showing tenant name, owner, plan, status, and action buttons" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

---

<a name="section-manage"></a>
## Managing Tenants

From the **Admin → Businesses** list, each row provides these actions:

| Action | Description |
|---|---|
| **Edit** | Update business details, owner, or subscription plan |
| **View Booking Page** | Opens their public `/{slug}` page in a new tab |
| **Impersonate Owner** | Log in as the tenant owner to debug their Business Panel |
| **Suspend** | Disables the tenant — booking page shows an "unavailable" message |
| **Reactivate** | Re-enables a suspended tenant |
| **Delete** | Permanently removes the tenant and **all their data** |

> {danger.fa-ban} **Delete is irreversible.** It permanently removes all services, providers, bookings, and client records for that tenant. Always confirm with the business owner before deleting.

---

<a name="section-flow"></a>
## Self-Registration Flow

When a new business registers via `/register`:

1. They fill in business name, slug, email, password, and choose a plan
2. **Free plan** → tenant is provisioned immediately, redirected to Business Panel
3. **Paid plan** → redirected to **Stripe Checkout**
4. Payment succeeds → Stripe sends `checkout.session.completed` webhook
5. Slotara receives the webhook → provisions the tenant automatically
6. Super admin receives a notification (if `new_registration` preference is enabled)
7. Business owner receives a welcome email

```
Register → Stripe Checkout → Webhook → Tenant Provisioned → Business Panel
```

> {warning.fa-exclamation-triangle} If a paid-plan tenant is not provisioned after payment, check **Admin → Activity Logs** for webhook errors. Verify your Stripe Webhook Signing Secret matches in both Stripe Dashboard and Admin → Settings → Payments.

---

<a name="section-suspend"></a>
## Suspending a Tenant

Suspending a tenant:

- Makes their public booking page unavailable (clients see a message)
- Does **not** delete any data
- Does **not** log out the tenant owner (they can still see their panel but cannot serve clients)

Reactivating restores everything immediately — no re-provisioning needed.

> {primary.fa-pause-circle} Use **suspension** instead of deletion for late payments or temporary closures. Data is preserved and the tenant can be reactivated instantly.
