<?php

use Illuminate\Support\Facades\DB;
use Webkul\Customer\Models\Customer;

it('authenticated dealer can view catalog', function () {
    $customer = createDealerCustomer();

    $response = $this->actingAs($customer, 'customer')
        ->get(route('dealer.catalog'));

    $response->assertStatus(200);
    $response->assertViewIs('rydeen-dealer::shop.catalog.index');
});

it('unauthenticated user is redirected from catalog', function () {
    $response = $this->get(route('dealer.catalog'));

    // Should be redirected by customer middleware
    $response->assertRedirect();
});

it('catalog search returns 200', function () {
    $customer = createDealerCustomer();

    $response = $this->actingAs($customer, 'customer')
        ->get(route('dealer.catalog', ['search' => 'test-sku']));

    $response->assertStatus(200);
});

it('catalog category filter returns 200', function () {
    $customer = createDealerCustomer();
    $categoryId = DB::table('categories')->value('id') ?? 1;

    $response = $this->actingAs($customer, 'customer')
        ->get(route('dealer.catalog', ['category' => $categoryId]));

    $response->assertStatus(200);
});

afterEach(function () {
    DB::table('customers')->where('email', 'like', 'catalog-test-%@example.com')->delete();
});

/**
 * Create a verified customer for testing.
 */
function createDealerCustomer(): Customer
{
    $email = 'catalog-test-' . uniqid() . '@example.com';
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
