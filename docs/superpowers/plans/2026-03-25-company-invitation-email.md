# Company Invitation Email Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** When an admin creates a company, automatically send a branded invitation email with a password-set link; provide a resend button in two admin UI surfaces.

**Architecture:** Event listener on `customer.registration.after` generates a Laravel password reset token and sends a Rydeen-branded `CompanyInvitationMail`. A resend route on `DealerApprovalController` regenerates the token and re-sends. The vendor `CompanyDataGrid` is overridden via container binding to add a resend row action.

**Tech Stack:** Laravel Mailables, Laravel Password Broker (`customers`), Bagisto Events, Pest tests

---

### Task 1: CompanyInvitationMail Mailable & Email Template

**Files:**
- Create: `packages/Rydeen/Dealer/src/Mail/CompanyInvitationMail.php`
- Create: `packages/Rydeen/Dealer/src/Resources/views/shop/emails/company-invitation.blade.php`

- [ ] **Step 1: Write the test for CompanyInvitationMail**

Create `packages/Rydeen/Dealer/tests/Unit/CompanyInvitationMailTest.php`:

```php
<?php

use Rydeen\Dealer\Mail\CompanyInvitationMail;

it('builds with correct subject and view', function () {
    $dealer = (object) [
        'first_name' => 'John',
        'last_name'  => 'Doe',
        'email'      => 'john@example.com',
    ];

    $mailable = new CompanyInvitationMail($dealer, 'https://example.com/reset-password/abc123?email=john@example.com');

    $mailable->assertHasSubject('Welcome to the Rydeen Dealer Portal');
    $mailable->assertSeeInHtml('John');
    $mailable->assertSeeInHtml('https://example.com/reset-password/abc123?email=john@example.com');
    $mailable->assertSeeInHtml('RYDEEN');
    $mailable->assertSeeInHtml('Set Your Password');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test packages/Rydeen/Dealer/tests/Unit/CompanyInvitationMailTest.php`
Expected: FAIL — class `CompanyInvitationMail` not found

- [ ] **Step 3: Create the Mailable class**

Create `packages/Rydeen/Dealer/src/Mail/CompanyInvitationMail.php`:

```php
<?php

namespace Rydeen\Dealer\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public $dealer,
        public string $resetUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to the Rydeen Dealer Portal',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'rydeen-dealer::shop.emails.company-invitation',
        );
    }
}
```

- [ ] **Step 4: Create the email template**

Create `packages/Rydeen/Dealer/src/Resources/views/shop/emails/company-invitation.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f7f7f7; margin: 0; padding: 20px;">
    <div style="max-width: 480px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 40px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h1 style="text-align: center; color: #1a1a1a; font-size: 24px; margin-bottom: 8px;">RYDEEN</h1>
        <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 32px;">Dealer Portal</p>

        <p style="color: #333; font-size: 16px;">Hi {{ $dealer->first_name }},</p>

        <p style="color: #333; font-size: 16px;">You've been invited to the Rydeen Dealer Portal. To get started, set your password by clicking the button below.</p>

        <div style="text-align: center; margin: 32px 0;">
            <a href="{{ $resetUrl }}" style="display: inline-block; background: #2563eb; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: bold;">Set Your Password</a>
        </div>

        <p style="color: #666; font-size: 14px;">Once you've set your password, you can log in at any time to browse our catalog, place orders, and access dealer resources.</p>

        <p style="color: #666; font-size: 14px; margin-top: 24px;">
            If you have any questions, please contact us at {{ config('rydeen.admin_order_email') }}.
        </p>

        <p style="color: #999; font-size: 12px; text-align: center; margin-top: 32px;">
            &mdash; Rydeen
        </p>
    </div>
</body>
</html>
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test packages/Rydeen/Dealer/tests/Unit/CompanyInvitationMailTest.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add packages/Rydeen/Dealer/src/Mail/CompanyInvitationMail.php \
       packages/Rydeen/Dealer/src/Resources/views/shop/emails/company-invitation.blade.php \
       packages/Rydeen/Dealer/tests/Unit/CompanyInvitationMailTest.php
git commit -m "feat: add CompanyInvitationMail mailable and Rydeen-branded email template"
```

---

### Task 2: CompanyInvitationListener (Auto-send on Creation)

**Files:**
- Create: `packages/Rydeen/Dealer/src/Listeners/CompanyInvitationListener.php`
- Modify: `packages/Rydeen/Dealer/src/Providers/EventServiceProvider.php:16-28`

- [ ] **Step 1: Write the test for the listener**

Create `packages/Rydeen/Dealer/tests/Feature/CompanyInvitationTest.php`:

```php
<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Rydeen\Dealer\Mail\CompanyInvitationMail;
use Rydeen\Dealer\Listeners\CompanyInvitationListener;
use Webkul\Customer\Models\Customer;

it('sends invitation email when a company-type customer is created', function () {
    Mail::fake();

    $channelId = DB::table('channels')->value('id') ?? 1;
    $groupId = DB::table('customer_groups')->value('id') ?? 1;

    $customerId = DB::table('customers')->insertGetId([
        'first_name'        => 'Company',
        'last_name'         => 'Admin',
        'email'             => 'company-' . uniqid() . '@example.com',
        'password'          => bcrypt('password'),
        'type'              => 'company',
        'customer_group_id' => $groupId,
        'channel_id'        => $channelId,
        'is_verified'       => 1,
        'status'            => 1,
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    $customer = Customer::find($customerId);

    $listener = new CompanyInvitationListener();
    $listener->afterCreated($customer);

    Mail::assertQueued(CompanyInvitationMail::class, function ($mail) use ($customer) {
        return $mail->hasTo($customer->email);
    });

    // Cleanup
    DB::table('customer_password_resets')->where('email', $customer->email)->delete();
    DB::table('customers')->where('id', $customerId)->delete();
});

it('does not send invitation for non-company customers', function () {
    Mail::fake();

    $channelId = DB::table('channels')->value('id') ?? 1;
    $groupId = DB::table('customer_groups')->value('id') ?? 1;

    $customerId = DB::table('customers')->insertGetId([
        'first_name'        => 'Regular',
        'last_name'         => 'Customer',
        'email'             => 'regular-' . uniqid() . '@example.com',
        'password'          => bcrypt('password'),
        'type'              => 'person',
        'customer_group_id' => $groupId,
        'channel_id'        => $channelId,
        'is_verified'       => 0,
        'status'            => 0,
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    $customer = Customer::find($customerId);

    $listener = new CompanyInvitationListener();
    $listener->afterCreated($customer);

    Mail::assertNothingQueued();

    // Cleanup
    DB::table('customers')->where('id', $customerId)->delete();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test packages/Rydeen/Dealer/tests/Feature/CompanyInvitationTest.php`
Expected: FAIL — class `CompanyInvitationListener` not found

- [ ] **Step 3: Create the listener**

Create `packages/Rydeen/Dealer/src/Listeners/CompanyInvitationListener.php`:

```php
<?php

namespace Rydeen\Dealer\Listeners;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Rydeen\Dealer\Mail\CompanyInvitationMail;

class CompanyInvitationListener
{
    public function afterCreated($customer): void
    {
        if ($customer->type !== 'company') {
            return;
        }

        $token = Password::broker('customers')->createToken($customer);

        $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($customer->email));

        try {
            Mail::to($customer->email)->send(new CompanyInvitationMail($customer, $resetUrl));
        } catch (\Exception $e) {
            report($e);
        }
    }
}
```

- [ ] **Step 4: Register the listener in EventServiceProvider**

Modify `packages/Rydeen/Dealer/src/Providers/EventServiceProvider.php` — add to the `$listen` array:

```php
'customer.registration.after' => [
    [CompanyInvitationListener::class, 'afterCreated'],
],
```

And add the import:

```php
use Rydeen\Dealer\Listeners\CompanyInvitationListener;
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test packages/Rydeen/Dealer/tests/Feature/CompanyInvitationTest.php`
Expected: PASS (both tests)

- [ ] **Step 6: Commit**

```bash
git add packages/Rydeen/Dealer/src/Listeners/CompanyInvitationListener.php \
       packages/Rydeen/Dealer/src/Providers/EventServiceProvider.php \
       packages/Rydeen/Dealer/tests/Feature/CompanyInvitationTest.php
git commit -m "feat: auto-send invitation email when admin creates a company"
```

---

### Task 3: Resend Invitation Route & Controller Method

**Files:**
- Modify: `packages/Rydeen/Dealer/src/Http/Controllers/Admin/DealerApprovalController.php:1-102`
- Modify: `packages/Rydeen/Dealer/src/Routes/admin.php:1-14`
- Modify: `packages/Rydeen/Dealer/src/Resources/lang/en/app.php:4-46`

- [ ] **Step 1: Write the test for the resend route**

Add to `packages/Rydeen/Dealer/tests/Feature/CompanyInvitationTest.php`:

```php
it('admin can resend invitation for a company', function () {
    Mail::fake();

    $admin = getTestAdmin();
    $channelId = DB::table('channels')->value('id') ?? 1;
    $groupId = DB::table('customer_groups')->value('id') ?? 1;

    $customerId = DB::table('customers')->insertGetId([
        'first_name'        => 'Resend',
        'last_name'         => 'Test',
        'email'             => 'resend-' . uniqid() . '@example.com',
        'password'          => bcrypt('password'),
        'type'              => 'company',
        'customer_group_id' => $groupId,
        'channel_id'        => $channelId,
        'is_verified'       => 1,
        'status'            => 1,
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->post(route('admin.rydeen.dealers.resend-invitation', $customerId));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertQueued(CompanyInvitationMail::class, function ($mail) use ($customerId) {
        $customer = Customer::find($customerId);
        return $mail->hasTo($customer->email);
    });

    // Cleanup
    $email = DB::table('customers')->where('id', $customerId)->value('email');
    DB::table('customer_password_resets')->where('email', $email)->delete();
    DB::table('customers')->where('id', $customerId)->delete();
});

it('resend invitation rejects non-company customers', function () {
    Mail::fake();

    $admin = getTestAdmin();
    $customerId = createPendingDealer(); // type is not 'company'

    $response = $this->actingAs($admin, 'admin')
        ->post(route('admin.rydeen.dealers.resend-invitation', $customerId));

    $response->assertRedirect();
    $response->assertSessionHas('error');

    Mail::assertNothingQueued();

    // Cleanup
    DB::table('customers')->where('id', $customerId)->delete();
});
```

Note: This test file reuses `getTestAdmin()` and `createPendingDealer()` from `DealerApprovalTest.php`. Add the same use statements at the top and helper functions at the bottom, OR add an import. Since Pest test files are flat PHP, copy the helpers into this file:

```php
// Add at bottom of CompanyInvitationTest.php — only if not already loaded
if (! function_exists('getTestAdmin')) {
    function getTestAdmin(): \Webkul\User\Models\Admin
    {
        $admin = \Webkul\User\Models\Admin::where('email', 'rydeen-test-admin@example.com')->first();

        if (! $admin) {
            $roleId = DB::table('roles')->value('id') ?? 1;
            $id = DB::table('admins')->insertGetId([
                'name'       => 'Test Admin',
                'email'      => 'rydeen-test-admin@example.com',
                'password'   => bcrypt('password'),
                'status'     => 1,
                'role_id'    => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $admin = \Webkul\User\Models\Admin::find($id);
        }

        return $admin;
    }
}

if (! function_exists('createPendingDealer')) {
    function createPendingDealer(): int
    {
        $channelId = DB::table('channels')->value('id') ?? 1;
        $groupId = DB::table('customer_groups')->value('id') ?? 1;

        return DB::table('customers')->insertGetId([
            'first_name'        => 'Pending',
            'last_name'         => 'Dealer',
            'email'             => 'pending-' . uniqid() . '@example.com',
            'password'          => bcrypt('password'),
            'customer_group_id' => $groupId,
            'channel_id'        => $channelId,
            'is_verified'       => 0,
            'status'            => 0,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test packages/Rydeen/Dealer/tests/Feature/CompanyInvitationTest.php`
Expected: FAIL — route `admin.rydeen.dealers.resend-invitation` not defined

- [ ] **Step 3: Add the route**

In `packages/Rydeen/Dealer/src/Routes/admin.php`, add inside the dealers route group (after the `update-forecast` route on line 13):

```php
Route::post('{id}/resend-invitation', [DealerApprovalController::class, 'resendInvitation'])->name('admin.rydeen.dealers.resend-invitation');
```

- [ ] **Step 4: Add the controller method**

In `packages/Rydeen/Dealer/src/Http/Controllers/Admin/DealerApprovalController.php`, add the import at the top:

```php
use Illuminate\Support\Facades\Password;
use Rydeen\Dealer\Mail\CompanyInvitationMail;
```

Add method after `updateForecastLevel()`:

```php
/**
 * Resend invitation email with a password-set link.
 */
public function resendInvitation(int $id)
{
    $customer = Customer::findOrFail($id);

    if ($customer->type !== 'company') {
        return redirect()->back()->with('error', trans('rydeen-dealer::app.admin.invitation-not-company'));
    }

    $token = Password::broker('customers')->createToken($customer);
    $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($customer->email));

    try {
        Mail::to($customer->email)->send(new CompanyInvitationMail($customer, $resetUrl));
    } catch (\Exception $e) {
        report($e);

        return redirect()->back()->with('error', trans('rydeen-dealer::app.admin.invitation-send-failed'));
    }

    return redirect()->back()->with('success', trans('rydeen-dealer::app.admin.invitation-sent'));
}
```

- [ ] **Step 5: Add translation strings**

In `packages/Rydeen/Dealer/src/Resources/lang/en/app.php`, add after the `'forecast-updated'` line (line 37):

```php
'invitation-sent'        => 'Invitation email has been sent.',
'invitation-send-failed' => 'Failed to send invitation email. Please try again.',
'invitation-not-company' => 'Invitations can only be sent to company accounts.',
'resend-invitation'      => 'Resend Invitation',
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test packages/Rydeen/Dealer/tests/Feature/CompanyInvitationTest.php`
Expected: PASS (all 4 tests)

- [ ] **Step 7: Commit**

```bash
git add packages/Rydeen/Dealer/src/Http/Controllers/Admin/DealerApprovalController.php \
       packages/Rydeen/Dealer/src/Routes/admin.php \
       packages/Rydeen/Dealer/src/Resources/lang/en/app.php \
       packages/Rydeen/Dealer/tests/Feature/CompanyInvitationTest.php
git commit -m "feat: add resend invitation route and controller method"
```

---

### Task 4: RydeenCompanyDataGrid Override (Resend Action in Grid)

**Files:**
- Create: `packages/Rydeen/Dealer/src/DataGrids/RydeenCompanyDataGrid.php`
- Modify: `packages/Rydeen/Dealer/src/Providers/DealerServiceProvider.php:17-21`

- [ ] **Step 1: Write the test for the DataGrid override**

Create `packages/Rydeen/Dealer/tests/Unit/RydeenCompanyDataGridTest.php`:

```php
<?php

use Rydeen\Dealer\DataGrids\RydeenCompanyDataGrid;
use Webkul\B2BSuite\DataGrids\Admin\CompanyDataGrid;

it('is bound in the container as the CompanyDataGrid', function () {
    $instance = app(CompanyDataGrid::class);
    expect($instance)->toBeInstanceOf(RydeenCompanyDataGrid::class);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test packages/Rydeen/Dealer/tests/Unit/RydeenCompanyDataGridTest.php`
Expected: FAIL — class `RydeenCompanyDataGrid` not found

- [ ] **Step 3: Create the DataGrid override**

Create `packages/Rydeen/Dealer/src/DataGrids/RydeenCompanyDataGrid.php`:

```php
<?php

namespace Rydeen\Dealer\DataGrids;

use Webkul\B2BSuite\DataGrids\Admin\CompanyDataGrid;

class RydeenCompanyDataGrid extends CompanyDataGrid
{
    /**
     * Prepare actions — inherit edit/delete from parent, add resend invitation.
     */
    public function prepareActions()
    {
        parent::prepareActions();

        if (bouncer()->hasPermission('customer.companies.edit')) {
            $this->addAction([
                'index'  => 'resend-invitation',
                'icon'   => 'icon-mail',
                'title'  => trans('rydeen-dealer::app.admin.resend-invitation'),
                'method' => 'POST',
                'url'    => function ($row) {
                    return route('admin.rydeen.dealers.resend-invitation', $row->customer_id);
                },
            ]);
        }
    }
}
```

- [ ] **Step 4: Register the container binding**

In `packages/Rydeen/Dealer/src/Providers/DealerServiceProvider.php`, add inside `register()` after the existing `$this->app->bind()` call:

```php
$this->app->bind(
    \Webkul\B2BSuite\DataGrids\Admin\CompanyDataGrid::class,
    \Rydeen\Dealer\DataGrids\RydeenCompanyDataGrid::class
);
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test packages/Rydeen/Dealer/tests/Unit/RydeenCompanyDataGridTest.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add packages/Rydeen/Dealer/src/DataGrids/RydeenCompanyDataGrid.php \
       packages/Rydeen/Dealer/src/Providers/DealerServiceProvider.php \
       packages/Rydeen/Dealer/tests/Unit/RydeenCompanyDataGridTest.php
git commit -m "feat: override CompanyDataGrid with resend invitation action"
```

---

### Task 5: Resend Button in Dealer Detail View

**Files:**
- Modify: `packages/Rydeen/Dealer/src/Resources/views/admin/dealers/view.blade.php:79-106`

- [ ] **Step 1: Write the test**

Add to `packages/Rydeen/Dealer/tests/Feature/CompanyInvitationTest.php`:

```php
it('dealer view page shows resend button for company accounts', function () {
    $admin = getTestAdmin();
    $channelId = DB::table('channels')->value('id') ?? 1;
    $groupId = DB::table('customer_groups')->value('id') ?? 1;

    $customerId = DB::table('customers')->insertGetId([
        'first_name'        => 'View',
        'last_name'         => 'Test',
        'email'             => 'view-' . uniqid() . '@example.com',
        'password'          => bcrypt('password'),
        'type'              => 'company',
        'customer_group_id' => $groupId,
        'channel_id'        => $channelId,
        'is_verified'       => 1,
        'status'            => 1,
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->get(route('admin.rydeen.dealers.view', $customerId));

    $response->assertStatus(200);
    $response->assertSee('Resend Invitation');

    // Cleanup
    DB::table('customers')->where('id', $customerId)->delete();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test packages/Rydeen/Dealer/tests/Feature/CompanyInvitationTest.php --filter="shows resend button"`
Expected: FAIL — "Resend Invitation" text not found in response

- [ ] **Step 3: Add the resend button to the view**

In `packages/Rydeen/Dealer/src/Resources/views/admin/dealers/view.blade.php`, replace the Actions section (lines 79-106) with:

```blade
    {{-- Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Approve / Reject --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-md font-semibold text-gray-800 dark:text-white mb-4">
                @lang('rydeen-dealer::app.admin.approval-actions')
            </h3>

            <div class="flex gap-3">
                @if (! $dealer->is_verified)
                    <form action="{{ route('admin.rydeen.dealers.approve', $dealer->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="primary-button">
                            @lang('rydeen-dealer::app.admin.approve')
                        </button>
                    </form>
                @endif

                @if (! $dealer->is_suspended)
                    <form action="{{ route('admin.rydeen.dealers.reject', $dealer->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                            @lang('rydeen-dealer::app.admin.reject')
                        </button>
                    </form>
                @endif

                @if ($dealer->type === 'company' && $dealer->is_verified && ! $dealer->is_suspended)
                    <form action="{{ route('admin.rydeen.dealers.resend-invitation', $dealer->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                            @lang('rydeen-dealer::app.admin.resend-invitation')
                        </button>
                    </form>
                @endif
            </div>
        </div>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test packages/Rydeen/Dealer/tests/Feature/CompanyInvitationTest.php --filter="shows resend button"`
Expected: PASS

- [ ] **Step 5: Run all invitation tests together**

Run: `php artisan test packages/Rydeen/Dealer/tests/Feature/CompanyInvitationTest.php`
Expected: All 5 tests PASS

- [ ] **Step 6: Commit**

```bash
git add packages/Rydeen/Dealer/src/Resources/views/admin/dealers/view.blade.php \
       packages/Rydeen/Dealer/tests/Feature/CompanyInvitationTest.php
git commit -m "feat: add resend invitation button to dealer detail view"
```

---

### Task 6: Full Test Suite Verification

**Files:** None (verification only)

- [ ] **Step 1: Run all Rydeen Dealer tests**

Run: `php artisan test packages/Rydeen/Dealer/`
Expected: All tests PASS — existing tests in DealerApprovalTest, CatalogTest, OrderFlowTest, DashboardStatsServiceTest, ProductListenerTest still pass alongside new invitation tests.

- [ ] **Step 2: Run full project test suite**

Run: `php artisan test`
Expected: No regressions. All test suites pass.

- [ ] **Step 3: Clear caches and verify**

Run: `php artisan optimize:clear`
Expected: All caches cleared successfully.

- [ ] **Step 4: Final commit (if any test fixes were needed)**

Only if adjustments were made during verification:

```bash
git add -A
git commit -m "fix: address test suite issues from invitation email feature"
```
