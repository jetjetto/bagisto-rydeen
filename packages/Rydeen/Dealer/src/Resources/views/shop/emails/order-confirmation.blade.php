<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        h1 { color: #1a1a1a; font-size: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #e5e5e5; font-size: 14px; }
        th { background: #f5f5f5; }
        .total { font-weight: bold; font-size: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Order Confirmation</h1>

        <p>Hi {{ $order->customer_first_name }},</p>

        <p>Thank you for your order. We have received it and it is being processed.</p>

        <p><strong>Order #:</strong> {{ $order->increment_id ?? $order->id }}<br>
        <strong>Date:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->sku }}</td>
                        <td>{{ (int) $item->qty_ordered }}</td>
                        <td>${{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="total">Grand Total: ${{ number_format($order->grand_total, 2) }}</p>

        <p>If you have any questions, please contact us at orders@rydeenmobile.com.</p>

        <p>— Rydeen Mobile</p>
    </div>
</body>
</html>
