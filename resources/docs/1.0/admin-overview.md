# Admin Panel Overview

---

- [Accessing the Panel](#section-access)
- [Panel Sections](#section-sections)
- [Navigation Badges](#section-badges)

<a name="section-access"></a>
## Accessing the Panel

Navigate to `https://yourdomain.com/admin`. Only users assigned the `super_admin` role can access this panel.

<img src="/docs/screenshots/admin-dashboard.png" alt="Super Admin panel dashboard — showing platform stats, recent activity, and sidebar navigation" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

---

<a name="section-sections"></a>
## Panel Sections

| Section | Path | Purpose |
|---|---|---|
| **Dashboard** | `/admin` | Platform health — total users, businesses, active subscriptions, recent signups |
| **Users** | `/admin/users` | All user accounts across all tenants |
| **Businesses** | `/admin/tenants` | All registered businesses (tenants) |
| **Subscription Plans** | `/admin/subscription-plans` | Create and manage pricing tiers |
| **Roles & Permissions** | `/admin/roles` | Fine-grained RBAC configuration |
| **Email Templates** | `/admin/email-templates` | Transactional email body content |
| **Email Layouts** | `/admin/email-template-layouts` | Shared outer HTML wrapper for all emails |
| **Inbox / Messages** | `/admin/contact-us` | Incoming contact form submissions |
| **Activity Logs** | `/admin/activity-logs` | Full audit trail of every action |
| **Login Activity** | `/admin/login-activities` | Login attempt history with IP and status |
| **Impersonation Logs** | `/admin/impersonation-logs` | Audit trail of tenant impersonation sessions |
| **IP Restrictions** | `/admin/ip-restrictions` | Allow or deny access by IP address |
| **Settings** | `/admin/settings` | All platform-wide configuration |
| **Notification Preferences** | `/admin/notification-preferences` | Choose which events you receive notifications for |
| **Password Policy** | `/admin/password-policy-settings` | Enforce password complexity, expiry, and history |
| **Profile** | `/admin/profile` | Your own admin account details |

---

<a name="section-badges"></a>
## Navigation Badges

Live counters appear next to sidebar items to surface important activity at a glance:

| Sidebar Item | Badge | Colour |
|---|---|---|
| Businesses | Total tenant count | <larecipe-badge type="primary">Purple</larecipe-badge> |
| Inbox / Messages | Count of unread **New** messages | <larecipe-badge type="danger">Red</larecipe-badge> |

> {primary.fa-inbox} The **Inbox** badge clears when messages are moved to In Progress or Resolved. Red = unread contact form submissions waiting for review.
