<?php

namespace Rydeen\Dealer\Http\Controllers\Shop;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Rydeen\Dealer\Models\DealerContact;

class ContactController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $customer = auth('customer')->user();
        $query = $request->get('q', '');

        $contacts = DealerContact::forDealer($customer->id)
            ->active()
            ->when($query, fn ($q) => $q->search($query))
            ->orderBy('first_name')
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'email', 'phone']);

        return response()->json($contacts);
    }

    public function store(Request $request): JsonResponse
    {
        $customer = auth('customer')->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'nullable|string|max:50',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $contact = DealerContact::create([
            ...$validated,
            'customer_id' => $customer->id,
        ]);

        return response()->json($contact, 201);
    }
}
