<?php

namespace Rydeen\Dealer\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Rydeen\Dealer\Models\DealerAddress;

class AddressController extends Controller
{
    public function index()
    {
        $customer = auth('customer')->user();
        $addresses = DealerAddress::forDealer($customer->id)
            ->orderByDesc('created_at')
            ->get();
        return view('rydeen-dealer::shop.addresses.index', compact('addresses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label'      => 'required|string|max:100',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'address1'   => 'required|string|max:255',
            'address2'   => 'nullable|string|max:255',
            'city'       => 'required|string|max:255',
            'state'      => 'required|string|max:255',
            'postcode'   => 'required|string|max:20',
            'country'    => 'nullable|string|max:2',
            'phone'      => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
        ]);

        $customer = auth('customer')->user();

        DealerAddress::create([
            'customer_id'  => $customer->id,
            'label'        => $request->label,
            'first_name'   => $request->first_name,
            'last_name'    => $request->last_name,
            'company_name' => $request->company_name,
            'address1'     => $request->address1,
            'address2'     => $request->address2,
            'city'         => $request->city,
            'state'        => $request->state,
            'postcode'     => $request->postcode,
            'country'      => $request->country ?? 'US',
            'phone'        => $request->phone,
            'is_approved'  => false,
            'is_default'   => false,
        ]);

        return redirect()->route('dealer.addresses')
            ->with('success', trans('rydeen-dealer::app.shop.addresses.created'));
    }

    public function destroy(int $id)
    {
        $customer = auth('customer')->user();
        $address = DealerAddress::where('id', $id)
            ->where('customer_id', $customer->id)
            ->firstOrFail();
        $address->delete();
        return redirect()->route('dealer.addresses')
            ->with('success', trans('rydeen-dealer::app.shop.addresses.deleted'));
    }
}
