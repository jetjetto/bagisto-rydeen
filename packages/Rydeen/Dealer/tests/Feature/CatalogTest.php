<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Webkul\Customer\Models\Customer;

it('authenticated dealer can view catalog', function () {
    ['customer' => $customer, 'uuid' => $uuid] = createDealerCustomer();

    $response = $this->actingAs($customer, 'customer')
        ->withCookie('rydeen_device', $uuid)
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
    ['customer' => $customer, 'uuid' => $uuid] = createDealerCustomer();

    $response = $this->actingAs($customer, 'customer')
        ->withCookie('rydeen_device', $uuid)
        ->get(route('dealer.catalog', ['search' => 'test-sku']));

    $response->assertStatus(200);
});

it('catalog category filter returns 200', function () {
    ['customer' => $customer, 'uuid' => $uuid] = createDealerCustomer();
    $categoryId = DB::table('categories')->value('id') ?? 1;

    $response = $this->actingAs($customer, 'customer')
        ->withCookie('rydeen_device', $uuid)
        ->get(route('dealer.catalog', ['category' => $categoryId]));

    $response->assertStatus(200);
});

afterEach(function () {
    DB::table('customers')->where('email', 'like', 'catalog-test-%@example.com')->delete();
});

/**
 * Create a verified customer with a trusted device record for testing.
 * Returns ['customer' => Customer, 'uuid' => string].
 */
function createDealerCustomer(): array
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

    $uuid = (string) Str::uuid();

    DB::table('rydeen_trusted_devices')->insert([
        'customer_id' => $id,
        'uuid'        => $uuid,
        'expires_at'  => now()->addDays(30),
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    return ['customer' => Customer::find($id), 'uuid' => $uuid];
}
