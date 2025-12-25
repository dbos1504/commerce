<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sales Report</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h1 style="color: #28a745; margin-top: 0;">Daily Sales Report</h1>
        <p><strong>Date:</strong> {{ $salesData['date'] }}</p>
    </div>

    <div style="background-color: #fff; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; margin-bottom: 20px;">
        <h2 style="margin-top: 0; color: #495057;">Summary</h2>
        <p><strong>Total Sales:</strong> ${{ number_format($salesData['totalSales'], 2) }}</p>
        <p><strong>Total Orders:</strong> {{ $salesData['totalOrders'] }}</p>
    </div>

    @if(count($salesData['productsSold']) > 0)
    <div style="background-color: #fff; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; margin-bottom: 20px;">
        <h2 style="margin-top: 0; color: #495057;">Products Sold</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #dee2e6;">Product</th>
                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #dee2e6;">Quantity</th>
                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #dee2e6;">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesData['productsSold'] as $product)
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">{{ $product['name'] }}</td>
                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #dee2e6;">{{ $product['quantity'] }}</td>
                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #dee2e6;">${{ number_format($product['revenue'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="background-color: #fff; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; margin-bottom: 20px;">
        <p style="color: #6c757d;">No products were sold today.</p>
    </div>
    @endif

    <hr style="border: none; border-top: 1px solid #dee2e6; margin: 20px 0;">

    <p style="color: #6c757d; font-size: 12px; text-align: center;">
        This is an automated daily sales report from the E-commerce System.
    </p>
</body>
</html>

