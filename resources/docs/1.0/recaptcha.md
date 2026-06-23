# reCAPTCHA v3

---

- [Overview](#section-overview)
- [Getting API Keys](#section-keys)
- [Configuration](#section-config)
- [How It Works](#section-how)
- [Score Threshold](#section-score)
- [Disabling reCAPTCHA](#section-disable)

<a name="section-overview"></a>
## Overview

Slotara uses **Google reCAPTCHA v3** to protect the contact form from spam bots. Unlike v2, reCAPTCHA v3 is **completely invisible** — there's no checkbox, no image challenge, no interruption. Google scores each submission in the background and Slotara rejects anything that looks like a bot.

<larecipe-badge type="success" rounded>Invisible</larecipe-badge> <larecipe-badge type="info" rounded>v3 Only</larecipe-badge> <larecipe-badge type="primary" rounded>Contact Form</larecipe-badge>

---

<a name="section-keys"></a>
## Getting API Keys

1. Go to [https://www.google.com/recaptcha/admin](https://www.google.com/recaptcha/admin)
2. Click the **+** (Create) button
3. Give it a label — e.g. `Slotara Production`
4. Select **reCAPTCHA type → Score based (v3)**
5. Under **Domains**, add your domain (e.g. `yourdomain.com`)
   - For local testing, also add `localhost`
6. Accept the Terms of Service and click **Submit**
7. Copy both the **Site Key** and **Secret Key**

> {warning.fa-exclamation-triangle} You must select **reCAPTCHA v3 (Score based)** specifically. v2 keys are not interchangeable with v3 — the verification API will fail silently if you use the wrong type.

<img src="/docs/screenshots/admin-settings-security.png" alt="Admin — Settings → Security tab where reCAPTCHA Site Key and Secret Key are entered" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

---

<a name="section-config"></a>
## Configuration

<larecipe-badge type="primary" rounded>Admin → Settings → Security</larecipe-badge>

Enter your keys in the admin panel:

| Field | Value |
|---|---|
| **Enable reCAPTCHA** | Toggle on to activate |
| **Site Key** | Starts with `6Le...` — used in the frontend JavaScript |
| **Secret Key** | Starts with `6Le...` — used for server-side verification only |

Or set directly in `.env`:

```env
RECAPTCHA_SITE_KEY=6LeXXXXXXXXXXXXXXXXXXXX
RECAPTCHA_SECRET_KEY=6LeXXXXXXXXXXXXXXXXXXXX
```

> {danger.fa-ban} The **Secret Key** must never appear in frontend JavaScript or client-side code. It is used only for server-to-server API calls. Exposing it allows attackers to bypass or forge reCAPTCHA tokens.

---

<a name="section-how"></a>
## How It Works

The entire process is invisible to the user — they never see a checkbox or a challenge.

```
User fills in the contact form
          ↓
Google silently scores the session in the background
          ↓
Form submitted
          ↓
    Score ≥ 0.5? (looks human?)
    /          \
  Yes           No
   ↓             ↓
Form accepted  Form rejected:
               "We couldn't verify your
                submission. Please try again."
```

Real users almost always score above 0.7. Only automated bots and suspicious sessions are blocked.

---

<a name="section-score"></a>
## Score Threshold

Google returns a score between `0.0` and `1.0` for each submission:

| Score | Interpretation |
|---|---|
| `1.0` | Very likely a human — confident interaction |
| `0.9` | Human — normal browsing pattern |
| `0.5` | **Slotara's threshold** — below this is rejected |
| `0.3` | Suspicious — possible bot or unusual pattern |
| `0.0` | Almost certainly automated |

<larecipe-progress type="success" :value="90">Likely human (0.9)</larecipe-progress>
<larecipe-progress type="warning" :value="50">Threshold (0.5)</larecipe-progress>
<larecipe-progress type="danger" :value="10">Bot (0.1)</larecipe-progress>

When a submission is blocked, the user sees a standard validation error:

```
We couldn't verify your submission. Please try again.
```

Most legitimate users score above 0.7. The 0.5 threshold provides strong bot protection without blocking real users.

> To change the threshold, set `RECAPTCHA_THRESHOLD` in your `.env` file (e.g. `RECAPTCHA_THRESHOLD=0.7` for stricter, `0.3` for more lenient).

---

<a name="section-disable"></a>
## Disabling reCAPTCHA

To disable reCAPTCHA (e.g. for development or internal-only deployments):

1. Go to **Admin → Settings → Security**
2. Toggle **Enable reCAPTCHA** off
3. Save

The contact form continues to work — all submissions are accepted without score checking. The reCAPTCHA script is not loaded in the browser when disabled.
