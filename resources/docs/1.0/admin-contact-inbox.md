# Contact Inbox

---

- [Navigation Badge](#section-badge)
- [Message Statuses](#section-statuses)
- [Replying to a Customer](#section-reply)
- [Bulk Actions](#section-bulk)
- [The Public Contact Form](#section-form)

<a name="section-badge"></a>
## Navigation Badge

The **Inbox** sidebar item shows a <larecipe-badge type="danger">red</larecipe-badge> badge with the count of unread **New** messages. The badge disappears when all messages are moved to **In Progress** or **Resolved**.

<img src="/docs/screenshots/admin-contact-inbox-list.png" alt="Admin — Contact Inbox list showing sender, subject, status, and unread badge count in sidebar" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

---

<a name="section-statuses"></a>
## Message Statuses

| Status | Meaning | Badge Colour |
|---|---|---|
| <larecipe-badge type="danger">New</larecipe-badge> | Just received, not yet acted on | Red |
| <larecipe-badge type="warning">In Progress</larecipe-badge> | Being reviewed or handled | Orange |
| <larecipe-badge type="success">Resolved</larecipe-badge> | Fully addressed and closed | Green |

Messages stay in the inbox permanently until explicitly deleted. Status is purely for your workflow — it does not notify the customer.

---

<a name="section-reply"></a>
## Replying to a Customer

<img src="/docs/screenshots/admin-contact-inbox-view.png" alt="Admin — Contact Inbox message view showing sender details, message body, and reply form" style="width:100%;border-radius:0.5rem;border:1px solid #e2e8f0;margin:1rem 0">

1. Find the message in **Admin → Inbox**
2. Click the **Reply** action button (paper plane icon)
3. Type your response in the modal
4. Click **Send Reply**

What happens automatically:

- Your reply and timestamp are saved to the database
- An email is sent to the customer using the `admin_contact_reply` template — including their original message and your response
- Message status is automatically set to **Resolved**
- The **Replied** column shows a <larecipe-badge type="success">green checkmark</larecipe-badge>

> {primary.fa-reply} You can reply multiple times. Each reply sends a fresh email. Change status back to **In Progress** to track follow-ups.

---

<a name="section-bulk"></a>
## Bulk Actions

Select multiple messages using the checkboxes, then use the bulk action menu:

| Action | Effect |
|---|---|
| **Mark as In Progress** | Sets all selected messages to In Progress |
| **Mark as Resolved** | Sets all selected messages to Resolved |
| **Delete** | Permanently removes the selected messages |

> {danger.fa-ban} Bulk Delete is permanent — there is no recycle bin. Mark messages as **Resolved** instead of deleting them if you want to keep the conversation history.

---

<a name="section-form"></a>
## The Public Contact Form

The contact form is available at `/contact`. It supports:

| Feature | Details |
|---|---|
| **Fields** | Name, Email, Phone, Inquiry Type, Message |
| **reCAPTCHA v3** | Invisible bot protection (when configured in Settings) |
| **Confirmation Email** | Sent to the submitter with the `contact_confirmation` template |
| **Admin Notification** | Email sent to the configured `contact_email` address |
| **Inbox Entry** | Message is saved to the database immediately |

> {warning.fa-cog} If contact submissions are not appearing in the inbox, verify your **queue worker** is running — inbox entries are dispatched as queued jobs.
