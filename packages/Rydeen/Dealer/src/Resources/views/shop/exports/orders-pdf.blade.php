<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f0; font-size: 11px; text-transform: uppercase; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Rydeen Dealer Orders</h1>
    <p class="meta">
        {{ $customer->first_name }} {{ $customer->last_name }} ({{ $customer->email }}) |
        Generated: {{ now()->format('M d, Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Date</th>
                <th>Status</th>
                <th>Items</th>
                <th class="text-right">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>{{ $order->increment_id ?? $order->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
                    <td>{{ $order->total_item_count }}</td>
                    <td class="text-right">${{ number_format($order->grand_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
