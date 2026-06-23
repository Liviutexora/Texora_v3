# User Management

---

- [User Roles](#section-roles)
- [Creating a User](#section-create)
- [Editing a User](#section-edit)
- [Impersonating a User](#section-impersonate)
- [Login Activity](#section-activity)

<a name="section-roles"></a>
## User Roles

Every user is assigned one or more roles that determine which panel they can access and what they can do inside it.

| Role | Panel Access | Capabilities |
|---|---|---|
| <larecipe-badge type="danger">super_admin</larecipe-badge> | `/admin` | Full platform control — bypasses all permission checks |
| <larecipe-badge type="primary">tenant_owner</larecipe-badge> | `/manage` | Manages their own business: services, providers, staff, bookings, settings |
| <larecipe-badge type="info">provider</larecipe-badge> | `/manage` | Bookable team member with their own working hours |
| <larecipe-badge type="warning">staff</larecipe-badge> | `/manage` | Can view and manage bookings only — no access to services, providers, or settings |
| <larecipe-badge type="success">client</larecipe-badge> | Public only | End customer who has completed a booking |

> {primary.fa-info-circle} Users can hold **multiple roles simultaneously**. A business owner can be both `tenant_owner` and `provider` — managing the business while appearing on the booking page.

---

<a name="section-create"></a>
## Creating a User

<img src="/docs/screenshots/admin-users-list.png" alt="Admin — Users list showing name, email, role, and tenant assignment" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

1. Go to **Admin → Users → New User**
2. Fill in **Name**, **Email**, and **Password**
3. Assign one or more **Roles**
4. Click **Save**

The user can log in immediately. A welcome email is sent if the `welcome_email` notification is enabled in your preferences.

---

<a name="section-edit"></a>
## Editing a User

Click any user row to open their profile. You can:

- Update their name, email, or password
- Add or remove roles
- View their linked tenant (if they are a business owner)
- Revoke all active sessions (forces re-login everywhere)

> {warning.fa-exclamation-triangle} Removing the `super_admin` role from your own account will immediately lock you out of the admin panel. Always ensure at least one other super admin exists before making role changes to your account.

---

<a name="section-impersonate"></a>
## Impersonating a User

Impersonation lets you log in as any user to troubleshoot their experience without knowing their password.

1. Find the user in **Admin → Users**
2. Click the **Impersonate** action button
3. You are immediately redirected to their panel (Business Panel or public frontend depending on their role)
4. A persistent banner at the top of the page shows who you are impersonating
5. Click **Stop Impersonating** to return to your super admin session

<img src="/docs/screenshots/business-dashboard.png" alt="Impersonation banner shown at the top of the Business Panel during an active impersonation session" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

All sessions are permanently logged in **Admin → Impersonation Logs** with: super admin, target user, start time, end time, and IP address.

> {danger.fa-eye} Impersonation is a privileged action. Every session is permanently logged. Only impersonate users for legitimate support purposes.

---

<a name="section-activity"></a>
## Login Activity

**Admin → Login Activity** records every authentication attempt with:

| Column | Description |
|---|---|
| User | Email address used |
| IP Address | Client IP at time of attempt |
| User Agent | Browser / device string |
| Status | `success` or `failed` |
| Date & Time | Timestamp of the attempt |

Use this log to investigate suspicious activity or repeated failed login attempts. Combine with **Admin → IP Restrictions** to block specific addresses.
