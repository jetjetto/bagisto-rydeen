<?php

namespace Rydeen\Dealer\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Webkul\Customer\Models\Customer;

class ImpersonationController extends Controller
{
    public function start(int $id)
    {
        $dealer = Customer::findOrFail($id);

        if (! $dealer->is_verified || $dealer->is_suspended) {
            return redirect()->back()->with('error', trans('rydeen-dealer::app.admin.impersonate-not-allowed'));
        }

        $admin = auth('admin')->user();

        session([
            'impersonating_admin_id'  => $admin->id,
            'impersonating_dealer_id' => $dealer->id,
        ]);

        auth('customer')->login($dealer);

        return redirect()->route('dealer.dashboard');
    }

    public function stop()
    {
        $dealerId = session('impersonating_dealer_id');

        auth('customer')->logout();

        session()->forget(['impersonating_admin_id', 'impersonating_dealer_id']);

        return redirect()->route('admin.rydeen.dealers.view', $dealerId);
    }
}
