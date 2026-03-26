<?php

namespace Rydeen\Dealer\Listeners;

use Illuminate\Support\Facades\Mail;
use Webkul\User\Models\Admin;
use Rydeen\Dealer\Mail\OrderConfirmationMail;
use Rydeen\Dealer\Mail\OrderSubmittedMail;

class OrderListener
{
    /**
     * Handle the event after an order is created.
     */
    public function afterOrderCreated($order): void
    {
        $this->addImpersonationAuditNote($order);

        // Send notification to admin
        try {
            $adminEmail = config('rydeen.admin_order_email', 'orders@test.reform9.com');
            Mail::to($adminEmail)->send(new OrderSubmittedMail($order));
        } catch (\Exception $e) {
            report($e);
        }

        // Send confirmation to dealer
        try {
            if (isset($order->customer_email) && $order->customer_email) {
                Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
            }
        } catch (\Exception $e) {
            report($e);
        }
    }

    protected function addImpersonationAuditNote($order): void
    {
        $adminId = session('impersonating_admin_id');

        if (! $adminId) {
            return;
        }

        $admin = Admin::find($adminId);
        $dealer = auth('customer')->user();

        $auditNote = sprintf(
            'Order placed by %s on behalf of %s %s',
            $admin?->name ?? 'Admin #' . $adminId,
            $dealer?->first_name ?? '',
            $dealer?->last_name ?? ''
        );

        $existingNotes = $order->notes;
        $order->notes = $existingNotes
            ? $existingNotes . "\n\n" . $auditNote
            : $auditNote;

        if (method_exists($order, 'save')) {
            $order->save();
        }
    }
}
