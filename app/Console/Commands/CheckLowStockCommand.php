<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:check-low-stock-command')]
#[Description('Command description')]
class CheckLowStockCommand extends Command
{
    protected $signature = 'inventory:check-low-stock';
    protected $description = 'Notify admins of low stock products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lowStockProducts = Product::whereColumn(
            'quantity', '<=', 'low_stock_threshold'
        )->get();

        if ($lowStockProducts->isEmpty()) {
            $this->info('All stock levels are healthy.');
            return;
        }

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new LowStockNotification($lowStockProducts));
        }

        $this->info("Notified {$admins->count()} admin(s) about " .
            "{$lowStockProducts->count()} low-stock item(s).");
    }
}
