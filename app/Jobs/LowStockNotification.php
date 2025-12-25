<?php

namespace App\Jobs;

use App\Mail\LowStockNotificationMail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class LowStockNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Product $product
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get admin user (dummy admin)
        $admin = User::where('email', 'admin@example.com')->first();

        if ($admin) {
            Mail::to($admin->email)->send(new LowStockNotificationMail($this->product));
        }
    }
}
