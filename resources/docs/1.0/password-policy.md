# Password Policy

---

- [Overview](#section-overview)
- [Policy Settings](#section-settings)
- [Enforcement Points](#section-enforcement)
- [Password History](#section-history)
- [Password Expiry](#section-expiry)

<a name="section-overview"></a>
## Overview

<larecipe-badge type="primary" rounded>Admin → Settings → Password Policy</larecipe-badge>

Slotara includes a configurable password policy that enforces complexity rules, prevents password reuse, and can force periodic password changes. Changes take effect immediately for all users.

<img src="/docs/screenshots/admin-settings-password-policy.png" alt="Admin — Settings → Password Policy tab showing complexity toggles, history, and expiry settings" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

---

<a name="section-settings"></a>
## Policy Settings

| Setting | Default | Description |
|---|---|---|
| **Minimum Length** | `8` | Minimum character count required |
| **Require Uppercase** | Off | At least one uppercase letter (A–Z) |
| **Require Lowercase** | Off | At least one lowercase letter (a–z) |
| **Require Numbers** | Off | At least one digit (0–9) |
| **Require Symbols** | Off | At least one special character (`!@#$%^&*`) |
| **Password History** | `0` | Number of previous passwords that cannot be reused |
| **Password Expiry (days)** | `0` | Days before a password must be changed (0 = never) |

**Example — Strong policy configuration:**

```
Minimum Length:   12
Require Uppercase: ✅
Require Lowercase: ✅
Require Numbers:   ✅
Require Symbols:   ✅
Password History:  5
Password Expiry:   90 days
```

> {primary.fa-info-circle} When **Minimum Length** is set, the registration form shows "Minimum X characters" in the placeholder, reducing failed attempts.

---

<a name="section-enforcement"></a>
## Enforcement Points

The policy is validated at every password entry point across the platform:

| Location | Policy Enforced |
|---|---|
| Registration page | ✅ Yes |
| Profile → Change Password | ✅ Yes |
| Forgot Password → Reset flow | ✅ Yes |
| Admin-created user accounts | ✅ Yes |


When a rule fails, the validation error names the specific rule that was violated:

```
✗ Password must be at least 12 characters.
✗ Password must contain at least one uppercase letter.
✗ Password must contain at least one symbol.
```

> {warning.fa-exclamation-triangle} Changing the policy does **not** invalidate existing users' passwords. Users with passwords that no longer meet the new rules will only be required to update on their next voluntary password change — unless **Password Expiry** is also set.

---

<a name="section-history"></a>
## Password History

When **Password History** is set to a value greater than `0`, the system stores a hash of each password the user has set. When they change their password, the new one is checked against their history.

| Setting | Effect |
|---|---|
| `0` | History check disabled — any password is accepted |
| `3` | Last 3 passwords cannot be reused |
| `10` | Last 10 passwords cannot be reused |

```
User tries to change password
          ↓
New password hashed
          ↓
Compared against stored history hashes
          ↓
    Match found?
    /          \
  Yes           No
   ↓             ↓
Rejected:     Accepted:
"Cannot       Password saved,
reuse recent  old one added to
passwords"    history
```

**History is applied at:**

- Profile → Change Password
- Forgot Password → Reset flow
- Admin-forced password change

---

<a name="section-expiry"></a>
## Password Expiry

When **Password Expiry** is set to a value greater than `0`, users must change their password after that many days.

**On login, if password has expired:**

1. User enters their email and password — credentials are valid
2. They are redirected to the **Change Password** page instead of their dashboard
3. A message explains: "Your password has expired. Please set a new one."
4. The new password must pass all current policy rules
5. After updating, they are logged in and redirected normally

<larecipe-badge type="danger" rounded>Expiry Active</larecipe-badge> — Users cannot access any part of the application until they change their password.

**Expiry is tracked per user** from the timestamp of their last `updatePassword()` call. It is not based on account creation date.

| Setting | Effect |
|---|---|
| `0` | Expiry disabled — passwords never expire |
| `30` | Passwords expire every 30 days |
| `90` | Passwords expire every 90 days (common policy) |
| `365` | Passwords expire annually |

> {primary.fa-lightbulb-o} Setting expiry to `90` days is a common enterprise password policy. For most SaaS applications, disabling expiry (`0`) and instead enforcing strong complexity rules provides a better user experience without sacrificing meaningful security.
