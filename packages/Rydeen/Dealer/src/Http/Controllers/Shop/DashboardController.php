<?php

namespace Rydeen\Dealer\Http\Controllers\Shop;

use Illuminate\Routing\Controller;
use Rydeen\Dealer\Services\DashboardStatsService;

class DashboardController extends Controller
{
    /**
     * Show the dealer dashboard.
     */
    public function index()
    {
        $customer = auth('customer')->user();
        $stats = app(DashboardStatsService::class)->getStats($customer);

        return view('rydeen-dealer::shop.dashboard.index', compact('stats', 'customer'));
    }
}
