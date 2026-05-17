<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckLowStockCommand extends Command
{
    protected $signature = 'inventory:check-low-stock';
    protected $description = 'Check for low stock products and notify admins';

    public function handle(): void
    {
        $lowStockProducts = Product::whereColumn('quantity', '<=', 'low_stock_threshold')
            ->where('is_active', true)
            ->get();

        if ($lowStockProducts->isEmpty()) {
            $this->info('All stock levels are healthy.');
            return;
        }

        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        $admins = $adminRole ? User::where('role_id', $adminRole->id)->get() : collect();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'warning',
                'title' => 'Low Stock Alert',
                'message' => "{$lowStockProducts->count()} product(s) are low on stock.",
                'related_model' => Product::class,
            ]);
        }

        // Also notify store managers
        $managerRole = \App\Models\Role::where('name', 'store_manager')->first();
        if ($managerRole) {
            $managers = User::where('role_id', $managerRole->id)->get();
            foreach ($managers as $manager) {
                Notification::create([
                    'user_id' => $manager->id,
                    'type' => 'warning',
                    'title' => 'Low Stock Alert',
                    'message' => "{$lowStockProducts->count()} product(s) are low on stock.",
                    'related_model' => Product::class,
                ]);
            }
        }

        // Send email if SMTP is configured
        try {
            $storeName = \App\Models\StoreSetting::getValue('store_name', 'QueenBuilders IMS');
            $emailRecipient = config('mail.from.address');

            if ($emailRecipient && $admins->isNotEmpty()) {
                $productList = $lowStockProducts->take(10)->map(fn($p) => "- {$p->name} (SKU: {$p->sku}) — Qty: {$p->quantity}")->implode("\n");
                $moreCount = max(0, $lowStockProducts->count() - 10);

                Mail::raw(
                    "LOW STOCK ALERT — {$storeName}\n\n" .
                    "The following products are low on stock:\n\n" .
                    "{$productList}" .
                    ($moreCount ? "\n\n...and {$moreCount} more product(s)." : "") .
                    "\n\nPlease review inventory and restock as needed.\n" .
                    "Login: " . url('/login'),
                    function ($message) use ($storeName, $emailRecipient) {
                        $message->to($emailRecipient)
                            ->subject("Low Stock Alert — {$storeName}");
                    }
                );
            }
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
        }

        $totalNotified = $admins->count() + ($managerRole ? User::where('role_id', $managerRole->id)->count() : 0);
        $this->info("Notified {$totalNotified} staff about {$lowStockProducts->count()} low-stock item(s).");
    }
}
