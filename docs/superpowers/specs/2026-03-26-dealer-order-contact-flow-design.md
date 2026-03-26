# Dealer Order Contact Flow & Notification Fan-Out

**Date:** 2026-03-26
**Status:** Approved

## Overview

When a dealer places an order, they must associate a customer contact (the end consumer who inquired about a product). This contact info triggers a fan-out of notification emails to all relevant parties. The client handles payments externally — emails are informational only.

## Requirements

1. Dealer must attach a customer contact before placing an order (required).
2. Dealers can search existing contacts or create new ones inline on the order-review page.
3. Contacts are per-dealer (private to each dealer).
4. On order placement, notification emails fire to: admin(s), assigned rep, dealer, and customer.
5. Emails are notification-only — no payment links, invoice attachments, or transactional CTAs.
6. Admin can configure which admin users receive order notification emails via an admin settings page.
7. Admin can view/edit all dealer contacts in a dedicated "Dealer Contacts" section of the admin panel.

## Data Model

### New table: `rydeen_dealer_contacts`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint, PK | auto-increment |
| customer_id | FK → customers | the dealer (company-type customer) who owns this contact |
| first_name | string | required |
| last_name | string | required |
| email | string | required |
| phone | string, nullable | optional |
| notes | text, nullable | freeform dealer notes |
| is_active | boolean, default true | admin can deactivate; inactive contacts hidden from dealer search |
| created_at | timestamp | |
| updated_at | timestamp | |

- No uniqueness constraint on email — same person can be a contact for multiple dealers.
- `customer_id` references the dealer's record in the `customers` table (where `type='company'`).

### New column on `orders` table

- `dealer_contact_id` (integer, FK → rydeen_dealer_contacts, nullable) — links the order to the customer contact. Nullable to preserve existing orders.

### New model: `DealerContact`

- `belongsTo` → `Customer` (the dealer)
- `hasMany` → `Order` (orders associated with this contact)
- The `Customer` (dealer) model gets a `hasMany` → `DealerContact` relationship.

## Order Review Page — Contact Widget

The existing order-review page (`/dealer/order-review`) gains a contact section above the order summary.

### Behavior

- **Search box** — Alpine.js typeahead hitting `GET /dealer/contacts/search?q={query}`. Shows matching contacts by name, email, or phone. Dealer clicks to select.
- **"Add New" toggle** — Expands an inline form: first name (required), last name (required), email (required), phone (optional), notes (optional). On save, creates via `POST /dealer/contacts` and auto-selects.
- **Selected contact display** — Card showing name/email/phone with a "Change" button.
- **Required gate** — "Place Order" button disabled until a contact is selected. Server-side validation enforces `dealer_contact_id` is present and belongs to the current dealer.

### New endpoints

- `GET /dealer/contacts/search?q={query}` — returns JSON array, scoped to current dealer's contacts.
- `POST /dealer/contacts` — creates new contact, returns JSON. Validates required fields.

### No standalone contacts page on dealer side

Contacts are created/selected only in the order-review context. The contact book builds organically. Admin manages contacts from the admin panel if needed.

## Email Notification Fan-Out

When an order is placed, four emails fire from the `OrderListener`:

| Recipient | Email class | Queued? | Content |
|-----------|------------|---------|---------|
| Admin(s) | `OrderSubmittedMail` (updated) | Yes | "Dealer X placed order #123 on behalf of Customer Y" — line items, totals, dealer info, customer contact info |
| Assigned rep | `OrderRepNotificationMail` (new) | Yes | Same core info as admin, addressed to the rep. Only fires if dealer has `assigned_rep_id`. |
| Dealer | `OrderConfirmationMail` (updated) | No (sync) | "Your order #123 has been submitted" — line items, totals, customer contact info |
| Customer | `OrderCustomerNotificationMail` (new) | Yes | "Your dealer has submitted an order on your behalf" — line items, totals, dealer name/contact. Intentionally light. |

### Email design notes

- All emails use the existing Rydeen branded template (yellow/black header, RYDEEN logo).
- Customer contact info (name, email, phone) appears in admin and rep emails for full context.
- Emails are notification-only — no payment buttons, no invoice attachments, no "click here to pay." Leaves room for the client's external invoicing/payment tool.

## Admin UI

### Order Notification Recipients Setting

- Located at `/admin/rydeen/settings` or as a section in existing Rydeen admin config.
- Multi-select dropdown listing all admin users. Admins check/uncheck who receives order notification emails.
- Stored via Bagisto's `core_config` table as a JSON array of admin user IDs.
- Falls back to `ADMIN_MAIL_ADDRESS` env var if no admins are selected.

### Dealer Contacts Section

- New admin sidebar item under the Customers menu: "Dealer Contacts."
- **Index page** — DataGrid listing all contacts across all dealers. Columns: contact name, email, phone, dealer name, order count, created date. Filterable by dealer.
- **View/edit** — Click through to edit contact details or see associated orders.
- **No create** — Contacts are created by dealers. Admins can view and edit but not create (preserves data ownership).

## Integration Points

### Existing code changes

- `OrderController::place()` — Add `dealer_contact_id` required validation. Load contact, attach FK to order.
- `OrderListener` — Extend fan-out from 2 emails (admin + dealer) to 4 (admin(s) + rep + dealer + customer).
- `OrderConfirmationMail` / `OrderSubmittedMail` — Update templates to include customer contact info block.
- Order view pages (dealer side + admin side) — Display the associated customer contact info.

### Edge cases

- **Contact edited after order placed** — Order detail shows current contact info (live reference). Past emails already sent. Acceptable for B2B context; snapshot can be added later if needed.
- **No dealer-side delete** — Dealers cannot delete contacts. Admins can deactivate if needed. Orders retain FK reference.
- **Rep not assigned** — Rep email silently skipped. No error.
- **No admin recipients configured** — Falls back to `ADMIN_MAIL_ADDRESS` env var.
- **Contact with no phone** — Templates gracefully omit the phone line.

## Out of Scope

- Payment processing, invoice generation, payment links in emails.
- Customer self-service portal or login.
- Contact import/export.
- Order status update emails to customer (future consideration).
