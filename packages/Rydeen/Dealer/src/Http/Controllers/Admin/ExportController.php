<?php

namespace Rydeen\Dealer\Http\Controllers\Admin;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Export dealer's orders as CSV.
     */
    public function csv(Request $request): StreamedResponse
    {
        $customer = auth('customer')->user();

        $orders = DB::table('orders')
            ->where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->get();

        $filename = 'orders_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Order #', 'Date', 'Status', 'Items', 'Grand Total']);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->increment_id ?? $order->id,
                    $order->created_at,
                    $order->status,
                    $order->total_item_count,
                    number_format($order->grand_total, 2),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Export dealer's orders as PDF.
     */
    public function pdf(Request $request)
    {
        $customer = auth('customer')->user();

        $orders = DB::table('orders')
            ->where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->get();

        $pdf = Pdf::loadView('rydeen-dealer::shop.exports.orders-pdf', [
            'orders'   => $orders,
            'customer' => $customer,
        ]);

        return $pdf->download('orders_' . date('Y-m-d') . '.pdf');
    }
}
