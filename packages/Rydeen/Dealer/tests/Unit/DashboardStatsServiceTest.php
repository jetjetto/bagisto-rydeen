<?php

use Illuminate\Support\Facades\DB;
use Rydeen\Dealer\Services\DashboardStatsService;

beforeEach(function () {
    $this->service = new DashboardStatsService();
});

it('returns correct stats structure', function () {
    $customer = createTestCustomer();
    $stats = $this->service->getStats($customer);

    expect($stats)->toHaveKeys([
        'total_orders_ytd',
        'this_month_total',
        'pending_orders_count',
        'forecast_level',
    ]);
});

it('returns zero values for customer with no orders', function () {
    $customer = createTestCustomer();
    $stats = $this->service->getStats($customer);

    expect($stats['total_orders_ytd'])->toBe(0);
    expect($stats['this_month_total'])->toBe(0.0);
    expect($stats['pending_orders_count'])->toBe(0);
});

it('returns forecast level when set', function () {
    $customer = createTestCustomer(['forecast_level' => 'Gold']);
    $stats = $this->service->getStats($customer);

    expect($stats['forecast_level'])->toBe('Gold');
});

it('returns N/A when forecast level is null', function () {
    $customer = createTestCustomer(['forecast_level' => null]);
    $stats = $this->service->getStats($customer);

    expect($stats['forecast_level'])->toBe('N/A');
});

it('counts orders correctly', function () {
    $customer = createTestCustomer();

    // Insert test orders
    $now = now();
    DB::table('orders')->insert([
        [
            'customer_id'         => $customer->id,
            'status'              => 'completed',
            'grand_total'         => 100.00,
            'base_grand_total'    => 100.00,
            'sub_total'           => 100.00,
            'base_sub_total'      => 100.00,
            'total_item_count'    => 1,
            'total_qty_ordered'   => 1,
            'is_guest'            => 0,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
            'channel_name'        => 'default',
            'increment_id'        => 'TEST-' . uniqid(),
            'created_at'          => $now,
            'updated_at'          => $now,
        ],
        [
            'customer_id'         => $customer->id,
            'status'              => 'pending',
            'grand_total'         => 200.00,
            'base_grand_total'    => 200.00,
            'sub_total'           => 200.00,
            'base_sub_total'      => 200.00,
            'total_item_count'    => 2,
            'total_qty_ordered'   => 2,
            'is_guest'            => 0,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
            'channel_name'        => 'default',
            'increment_id'        => 'TEST-' . uniqid(),
            'created_at'          => $now,
            'updated_at'          => $now,
        ],
    ]);

    $stats = $this->service->getStats($customer);

    expect($stats['total_orders_ytd'])->toBe(2);
    expect($stats['this_month_total'])->toBe(300.00);
    expect($stats['pending_orders_count'])->toBe(1);

    // Clean up
    DB::table('orders')->where('customer_id', $customer->id)->delete();
});

afterEach(function () {
    // Clean up test customers created during tests
    DB::table('customers')->where('email', 'like', 'dealer-test-%@example.com')->delete();
});

/**
 * Create a test customer (without RefreshDatabase).
 */
function createTestCustomer(array $attributes = []): object
{
    $email = 'dealer-test-' . uniqid() . '@example.com';
    $channelId = DB::table('channels')->value('id') ?? 1;
    $groupId = DB::table('customer_groups')->value('id') ?? 1;

    $id = DB::table('customers')->insertGetId(array_merge([
        'first_name'        => 'Test',
        'last_name'         => 'Dealer',
        'email'             => $email,
        'password'          => bcrypt('password'),
        'customer_group_id' => $groupId,
        'channel_id'        => $channelId,
        'is_verified'       => 1,
        'status'            => 1,
        'forecast_level'    => null,
        'created_at'        => now(),
        'updated_at'        => now(),
    ], $attributes, ['email' => $email]));

    return DB::table('customers')->where('id', $id)->first();
}
