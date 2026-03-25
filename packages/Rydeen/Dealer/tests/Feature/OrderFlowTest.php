<?php

use Illuminate\Support\Facades\DB;
use Webkul\Customer\Models\Customer;

it('authenticated dealer can view order review', function () {
    $customer = createOrderTestCustomer();

    $response = $this->actingAs($customer, 'customer')
        ->get(route('dealer.order-review'));

    $response->assertStatus(200);
    $response->assertViewIs('rydeen-dealer::shop.order-review.index');
});

it('authenticated dealer can view orders page', function () {
    $customer = createOrderTestCustomer();

    $response = $this->actingAs($customer, 'customer')
        ->get(route('dealer.orders'));

    $response->assertStatus(200);
    $response->assertViewIs('rydeen-dealer::shop.orders.index');
});

it('place order with empty cart redirects to catalog', function () {
    $customer = createOrderTestCustomer();

    $response = $this->actingAs($customer, 'customer')
        ->post(route('dealer.order-review.place'));

    $response->assertRedirect(route('dealer.catalog'));
});

it('unauthenticated user cannot view orders', function () {
    $response = $this->get(route('dealer.orders'));

    $response->assertRedirect();
});

it('authenticated dealer can view dashboard', function () {
    $customer = createOrderTestCustomer();

    $response = $this->actingAs($customer, 'customer')
        ->get(route('dealer.dashboard'));

    $response->assertStatus(200);
    $response->assertViewIs('rydeen-dealer::shop.dashboard.index');
});

it('authenticated dealer can view resources', function () {
    $customer = createOrderTestCustomer();

    $response = $this->actingAs($customer, 'customer')
        ->get(route('dealer.resources'));

    $response->assertStatus(200);
    $response->assertViewIs('rydeen-dealer::shop.resources.index');
});

afterEach(function () {
    DB::table('customers')->where('email', 'like', 'order-test-%@example.com')->delete();
});

/**
 * Create a verified customer for order testing.
 */
function createOrderTestCustomer(): Customer
{
    $email = 'order-test-' . uniqid() . '@example.com';
    $channelId = DB::table('channels')->value('id') ?? 1;
    $groupId = DB::table('customer_groups')->value('id') ?? 1;

    $id = DB::table('customers')->insertGetId([
        'first_name'        => 'Test',
        'last_name'         => 'Dealer',
        'email'             => $email,
        'password'          => bcrypt('password'),
        'customer_group_id' => $groupId,
        'channel_id'        => $channelId,
        'is_verified'       => 1,
        'status'            => 1,
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    return Customer::find($id);
}
