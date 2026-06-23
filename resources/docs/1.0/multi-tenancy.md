# Multi-Tenancy

---

- [How It Works](#section-how)
- [Tenant Isolation](#section-isolation)
- [Public Booking Pages](#section-public)
- [Adding Tenants Manually](#section-manual)
- [For Developers](#section-dev)

<a name="section-how"></a>
## How It Works

**Multi-tenancy** means you run one installation of Slotara and host unlimited businesses on it — each business gets their own isolated workspace, booking page, and data. They can never see each other's information.

Think of it like a shared office building: every tenant has their own locked office (their data), but the building infrastructure (the platform) is shared.

```
┌─────────────────────────────────────────────────────┐
│                   One Slotara Install               │
│                                                     │
│  ┌─────────────────┐    ┌─────────────────────┐    │
│  │   Salon A data  │    │   Clinic B data     │    │
│  │   (isolated)    │    │   (isolated)        │    │
│  └─────────────────┘    └─────────────────────┘    │
│                                                     │
│      Each business sees only their own data         │
└─────────────────────────────────────────────────────┘
```

> {danger.fa-ban} Only **Super Admins** can see data across all businesses. Never share the `/admin` panel access with business owners — they should only log in at `/manage`.

---

<a name="section-isolation"></a>
## Tenant Isolation

Every major resource is fully isolated per tenant:

| Resource | Isolated | Storage |
|---|---|---|
| Services | ✅ Yes | `tenant_id` scoped |
| Providers | ✅ Yes | `tenant_id` scoped |
| Staff | ✅ Yes | `tenant_id` scoped |
| Bookings | ✅ Yes | `tenant_id` scoped |
| Clients | ✅ Yes | `tenant_id` scoped |
| Email Templates | ✅ Yes | `tenant_id` scoped |
| Business Settings | ✅ Yes | `tenant_id` scoped |
| Uploaded Files | ✅ Yes | Stored under `storage/{tenant_id}/` prefix |
| User Accounts | ⚠️ Shared | Users can belong to multiple tenants |
| Subscription Plans | ❌ Global | Managed by Super Admin, shared across all tenants |

> {primary.fa-info-circle} User accounts are platform-level. A single user can be a `provider` in Tenant A and a `tenant_owner` in Tenant B — roles are tenant-scoped, user records are not.

---

<a name="section-public"></a>
## Public Booking Pages

Every tenant gets a unique public URL:

```
https://yourdomain.com/{slug}
```

The `slug` is set at tenant creation and must be **unique across the entire platform**. It cannot be changed after creation without updating the URL in the booking page route.

**Slug requirements:**

- Lowercase letters, numbers, and hyphens only
- No spaces or special characters
- Examples: `my-salon`, `john-photography`, `city-dentist`

> {warning.fa-globe} The booking page is publicly accessible to anyone with the URL. Security-by-obscurity is not a substitute for proper access controls.

---

<a name="section-manual"></a>
## Adding Tenants Manually

While most tenants register through the public sign-up page, super admins can provision a tenant directly:

1. Go to **Admin → Businesses → New Business**
2. Fill in:
   - **Business Name** — displayed in the Business Panel and emails
   - **Slug** — unique URL identifier (`/{slug}`)
   - **Owner** — select or create the owner user account
   - **Subscription Plan** — choose from active plans
3. Click **Save** — the tenant is provisioned immediately

**What provisioning does:**

```
Admin clicks Save
      ↓
Tenant record created
      ↓
Owner user linked (created if new)
      ↓
Default business settings initialised
      ↓
Welcome email sent to owner
      ↓
Owner can access /manage immediately
```

> Manually provisioned tenants are not billed through Stripe automatically. Manage their billing separately from your Stripe Dashboard if required.

---

<a name="section-dev"></a>
## For Developers

> {primary.fa-code} This section is for developers extending or customising Slotara.

**How isolation works under the hood**

Slotara uses single-database multi-tenancy. Every tenant-aware table has a `tenant_id` column. A global Eloquent scope (`TenantScope`) automatically appends `WHERE tenant_id = X` to every query in the Business Panel — no manual filtering needed in controllers.

```php
// You write:
$services = Service::all();

// What runs:
// SELECT * FROM services WHERE tenant_id = 5
```

**Models NOT scoped** (global, shared across all tenants):

- `User` — platform-wide accounts
- `SubscriptionPlan` — plan definitions
- `Role` / `Permission` — permission records

**TenantContext helper**

```php
use App\Support\TenantContext;

$tenant   = TenantContext::current(); // full Tenant model (or null outside Business Panel)
$tenantId = TenantContext::id();      // int or null

if (TenantContext::current()) {
    // Running inside the Business Panel — always non-null here
}
```
