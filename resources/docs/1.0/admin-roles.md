# Roles & Permissions

---

- [Default Roles](#section-roles)
- [Editing Permissions](#section-edit)
- [Super Admin Bypass](#section-bypass)
- [Notification Preferences](#section-notifications)

<a name="section-roles"></a>
## Default Roles

Slotara ships with five built-in roles. Each role controls which panels a user can access and what actions they can take.

| Role | Panel | What They Can Do |
|---|---|---|
| <larecipe-badge type="danger">super_admin</larecipe-badge> | `/admin` | Full platform control — bypasses **all** permission checks |
| <larecipe-badge type="primary">tenant_owner</larecipe-badge> | `/manage` | Manages their business: services, providers, staff, bookings, settings |
| <larecipe-badge type="info">provider</larecipe-badge> | `/manage` | Bookable team member with their own schedule |
| <larecipe-badge type="warning">staff</larecipe-badge> | `/manage` | Read/manage bookings only — no services, providers, or settings access |
| <larecipe-badge type="success">client</larecipe-badge> | Public only | End customer who has booked a service |

> {primary.fa-users} Users can hold **multiple roles**. A tenant owner who takes bookings can be both `tenant_owner` and `provider` simultaneously.

---

<a name="section-edit"></a>
## Editing Permissions

<larecipe-badge type="primary" rounded>Admin → Roles</larecipe-badge>

1. Click **Edit** on any role
2. Toggle individual permissions per resource
3. Save — changes take effect immediately for all users with that role

Each resource has granular on/off toggles:

| Permission | What It Controls |
|---|---|
| **View list** | See the list of all records |
| **View detail** | Open and read a single record |
| **Create** | Add new records |
| **Edit** | Modify existing records |
| **Delete** | Delete a single record |
| **Bulk delete** | Delete multiple records at once |

<img src="/docs/screenshots/admin-roles.png" alt="Admin — Roles list with edit permission matrix for each resource and action" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

> {warning.fa-exclamation-triangle} Avoid removing permissions from `tenant_owner` unless intentional. Missing permissions can cause blank pages or errors in the Business Panel.

---

<a name="section-bypass"></a>
## Super Admin Bypass

The `super_admin` role does **not** go through the permission check layer at all. Even if all permissions are accidentally removed from the role, super admins retain complete access to every panel and resource.

This is by design — it prevents accidental lockout of the platform owner.

> {danger.fa-ban} Keep the number of `super_admin` users to a minimum. Every super admin can see all tenant data, impersonate any user, and modify billing. Assign `staff` or `tenant_owner` roles to team members who only need partial access.

---

<a name="section-notifications"></a>
## Notification Preferences

Each admin user controls which events they are notified about at **Admin → Notification Preferences**.

| Event | Triggered By |
|---|---|
| `new_registration` | A new user registers on the platform |
| `welcome_email` | Sent to the new user automatically on registration |
| `reset_password_confirmation` | A user completes a password reset |
| `contact_confirmation` | A visitor submits the contact form |
| `new_contact_enquiry` | Admin alert for a new contact form submission |

Toggle each event per channel (Email / In-App) independently.
