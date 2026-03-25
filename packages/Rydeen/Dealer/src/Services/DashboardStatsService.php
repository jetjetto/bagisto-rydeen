<?php

namespace Rydeen\Dealer\Services;

use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    /**
     * Get dashboard statistics for a given customer.
     */
    public function getStats($customer): array
    {
        $year = now()->year;
        $month = now()->month;

        $baseQuery = DB::table('orders')->where('customer_id', $customer->id);

        return [
            'total_orders_ytd'    => (clone $baseQuery)->whereYear('created_at', $year)->count(),
            'this_month_total'    => (float) (clone $baseQuery)->whereYear('created_at', $year)->whereMonth('created_at', $month)->sum('grand_total'),
            'pending_orders_count' => (clone $baseQuery)->whereIn('status', ['pending', 'pending_payment', 'processing'])->count(),
            'forecast_level'       => $customer->forecast_level ?? 'N/A',
        ];
    }
}
