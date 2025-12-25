<?php

namespace App\Console\Commands;

use App\Mail\DailySalesReportMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class DailySalesReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:daily-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily sales report to admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->startOfDay();
        $tomorrow = now()->endOfDay();

        // Get all orders from today
        $orders = Order::whereBetween('created_at', [$today, $tomorrow])
            ->with(['items.product'])
            ->get();

        // Get admin user
        $admin = User::where('email', 'admin@example.com')->first();

        if (!$admin) {
            $this->error('Admin user not found. Please create a user with email: admin@example.com');
            return 1;
        }

        // Calculate sales summary
        $totalSales = $orders->sum('total');
        $totalOrders = $orders->count();
        $productsSold = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $productId = $item->product_id;
                $productName = $item->product->name;

                if (!isset($productsSold[$productId])) {
                    $productsSold[$productId] = [
                        'name' => $productName,
                        'quantity' => 0,
                        'revenue' => 0,
                    ];
                }

                $productsSold[$productId]['quantity'] += $item->quantity;
                $productsSold[$productId]['revenue'] += $item->subtotal;
            }
        }

        // Send email
        Mail::to($admin->email)->send(new DailySalesReportMail([
            'date' => $today->format('Y-m-d'),
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'productsSold' => $productsSold,
        ]));

        $this->info('Daily sales report sent to ' . $admin->email);

        return 0;
    }
}
