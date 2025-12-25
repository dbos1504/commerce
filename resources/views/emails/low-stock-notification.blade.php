<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Alert</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h1 style="color: #dc3545; margin-top: 0;">Low Stock Alert</h1>
        <p>Hello Admin,</p>
        <p>The following product is running low on stock:</p>
    </div>

    <div style="background-color: #fff; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; margin-bottom: 20px;">
        <h2 style="margin-top: 0; color: #495057;">{{ $product->name }}</h2>
        <p><strong>Current Stock:</strong> {{ $product->stock_quantity }} units</p>
        <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
    </div>

    <p style="color: #6c757d; font-size: 14px;">
        Please consider restocking this product soon.
    </p>

    <hr style="border: none; border-top: 1px solid #dee2e6; margin: 20px 0;">

    <p style="color: #6c757d; font-size: 12px; text-align: center;">
        This is an automated notification from the E-commerce System.
    </p>
</body>
</html>

