Daily Sales Report

Date: {{ $salesData['date'] }}

Summary:
---------
Total Sales: ${{ number_format($salesData['totalSales'], 2) }}
Total Orders: {{ $salesData['totalOrders'] }}

@if(count($salesData['productsSold']) > 0)
Products Sold:
--------------
@foreach($salesData['productsSold'] as $product)
- {{ $product['name'] }}: {{ $product['quantity'] }} units - ${{ number_format($product['revenue'], 2) }}
@endforeach
@else
No products were sold today.
@endif

---
This is an automated daily sales report from the E-commerce System.

