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
        $statsService = app(DashboardStatsService::class);
        $stats = $statsService->getStats($customer);
        $recentOrders = $statsService->getRecentOrders($customer->id);

        return view('rydeen-dealer::shop.dashboard.index', compact('customer', 'stats', 'recentOrders'));
    }
}
