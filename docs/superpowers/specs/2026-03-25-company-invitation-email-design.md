# Company Invitation Email System

**Date:** 2026-03-25
**Scope:** Admin-created companies only (not self-registered dealers)

## Problem

When an admin creates a company via `/admin/companies`, the B2B Suite generates a random bcrypted password that is never displayed or emailed. The company has no way to know they have an account and no way to log in without manually being told to use "Forgot Password."

## Solution

Use Laravel's built-in password reset token infrastructure to send a branded invitation email when a company is created. Provide a resend mechanism in two places in the admin UI.

## Approach

**Password Reset Token Link** — When a company is created, an event listener generates a password reset token via `Password::broker('customers')->createToken()` and emails a branded "Welcome — Set Your Password" email. The email links to the existing `/reset-password/{token}?email=...` page. No new DB tables or custom token logic needed.

## Architecture

### 1. Auto-send on Company Creation

- **Trigger:** `customer.registration.after` event (dispatched by B2B `CompanyController::store()`)
- **Listener:** `CompanyInvitationListener` checks `$customer->type === 'company'`, generates token, sends mail
- **Registration:** Via `Rydeen\Dealer\Providers\EventServiceProvider`

### 2. Mailable & Email Template

- **Mailable:** `CompanyInvitationMail` — accepts `$customer` and `$resetUrl`, implements `ShouldQueue`
- **Template:** `company-invitation.blade.php` — Rydeen-branded (matches `dealer-approved.blade.php` styling: RYDEEN header, Dealer Portal subheading, same fonts/colors/layout)
- **Content:** "You've been invited to the Rydeen Dealer Portal" with a "Set Your Password" button

### 3. Resend Invitation Route & Controller

- **Route:** `POST /admin/rydeen/dealers/{id}/resend-invitation`
- **Controller:** `DealerApprovalController::resendInvitation()` — finds customer, verifies `type === 'company'`, generates fresh token, sends `CompanyInvitationMail`, redirects with success flash
- **Added to:** existing `packages/Rydeen/Dealer/src/Routes/admin.php`

### 4. Admin UI — Two Surfaces

**A. CompanyDataGrid row action:**
- Custom `RydeenCompanyDataGrid` extends vendor `CompanyDataGrid`
- Adds a third action (envelope icon) alongside edit/delete
- Bound via Laravel container in `DealerServiceProvider` to override vendor DataGrid

**B. Dealer detail view:**
- "Resend Invitation" button on `rydeen-dealer::admin.dealers.view`
- Only shown for companies that are verified/active (not pending self-registered dealers)

## File Changes

All within `packages/Rydeen/Dealer/src/`:

| File | Action |
|------|--------|
| `Listeners/CompanyInvitationListener.php` | **New** — listens to `customer.registration.after`, sends invitation for `type=company` |
| `Mail/CompanyInvitationMail.php` | **New** — Mailable with Rydeen branding |
| `Resources/views/shop/emails/company-invitation.blade.php` | **New** — email template matching Rydeen style |
| `Providers/EventServiceProvider.php` | **Update** — register the new listener |
| `Providers/DealerServiceProvider.php` | **Update** — bind custom DataGrid override |
| `Http/Controllers/Admin/DealerApprovalController.php` | **Update** — add `resendInvitation()` method |
| `Routes/admin.php` | **Update** — add resend-invitation route |
| `DataGrids/RydeenCompanyDataGrid.php` | **New** — extends vendor CompanyDataGrid, adds resend action |
| `Resources/views/admin/dealers/view.blade.php` | **Update** — add resend button for active companies |
| `Resources/lang/en/app.php` | **Update** — add translation strings |

## Constraints

- Zero vendor modifications — uses event listeners and container binding overrides
- Password reset tokens use Laravel's `customers` broker (existing `customer_password_resets` table)
- Tokens auto-expire per Laravel config (default 60 minutes)
- Email is queued (`ShouldQueue`) to avoid blocking admin actions
