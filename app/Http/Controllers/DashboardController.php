<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\StockTransaction;
use App\Models\User;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\ProductVariant;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $role = auth()->user()->role->name;
        $now = now();

        // Shared base stats
        $stats = [
            'total_products'     => Product::active()->count(),
            'low_stock_count'    => Product::active()->lowStock()->count(),
            'total_categories'   => Category::active()->count(),
            'total_suppliers'    => Supplier::active()->count(),
            'out_of_stock'       => Product::active()->where('quantity', 0)->count(),
            'total_users'        => User::where('is_active', true)->count(),
            'total_roles'        => \App\Models\Role::where('is_active', true)->count(),
            'total_sales_today'  => PosTransaction::where('status', 'completed')
                                        ->whereDate('transaction_date', today())
                                        ->sum('total'),
            'transactions_today' => PosTransaction::where('status', 'completed')
                                        ->whereDate('transaction_date', today())
                                        ->count(),
            'sales_this_month'   => PosTransaction::where('status', 'completed')
                                        ->whereMonth('transaction_date', $now->month)
                                        ->whereYear('transaction_date', $now->year)
                                        ->sum('total'),
            'audit_logs_today'   => AuditLog::whereDate('created_at', today())->count(),
        ];

        // Role-specific data
        $lowStockProducts   = collect();
        $chartData          = collect();
        $salesChartData     = collect();
        $recentActivities   = collect();
        $topProducts        = collect();
        $recentSales        = collect();
        $recentTransactions = collect();

        // New analytics
        $categoryDistribution  = collect();
        $paymentBreakdown      = collect();
        $inventoryValue        = 0;
        $variantProductsCount  = 0;
        $profitEstimate        = 0;
        $lastMonthSales        = 0;
        $topCategories         = collect();
        $lowStockEntries       = collect();

        if ($role === 'inventory_manager') {
            $lowStockProducts = Product::with('category')->active()->lowStock()->orderBy('quantity')->limit(8)->get();

            $chartData = StockTransaction::selectRaw(
                "DATE(created_at) as date,
                SUM(CASE WHEN type = 'stock_in' THEN quantity ELSE 0 END) as stock_in,
                SUM(CASE WHEN type = 'stock_out' THEN quantity ELSE 0 END) as stock_out"
            )->where('created_at', '>=', now()->subDays(7))
                ->groupByRaw('DATE(created_at)')
                ->orderByRaw('DATE(created_at)')
                ->get();

            // Category distribution (for doughnut chart)
            $categoryDistribution = Product::active()
                ->with('category')
                ->get()
                ->groupBy(fn($p) => $p->category->name ?? 'Uncategorized')
                ->map(fn($items) => $items->count())
                ->sortDesc();

            // Inventory value: sum of (total_stock × cost)
            $products = Product::active()->with('variants')->get();
            $inventoryValue = $products->sum(function ($p) {
                $totalQty = $p->getTotalVariantQuantity();
                return $totalQty * (float) $p->cost;
            });

            $variantProductsCount = Product::active()->has('variants')->count();

            // Low stock entries: combine base products and variants
            $lowStockEntries = collect();

            // Add low-stock base products (no variants or base qty low)
            foreach (Product::active()->lowStock()->with('category')->orderBy('quantity')->limit(5)->get() as $p) {
                $lowStockEntries->push([
                    'type'    => 'product',
                    'sku'     => $p->sku,
                    'name'    => $p->name,
                    'category'=> $p->category->name ?? '-',
                    'qty'     => $p->quantity,
                    'threshold' => $p->low_stock_threshold,
                ]);
            }

            // Add low-stock variants
                        foreach (ProductVariant::active()->lowStock()
                ->with('product.category')
                ->orderBy('quantity')
                ->limit(5)->get() as $v) {
                $lowStockEntries->push([
                    'type'    => 'variant',
                    'sku'     => $v->sku,
                    'name'    => ($v->product->name ?? '?') . ' — ' . $v->name,
                    'category'=> $v->product->category->name ?? '-',
                    'qty'     => $v->quantity,
                    'threshold' => $v->low_stock_threshold,
                ]);
            }

            $lowStockEntries = $lowStockEntries->sortBy('qty')->take(8)->values();
        }

        if ($role === 'store_manager') {
            $topProducts = PosTransactionItem::select('product_name')
                ->selectRaw('SUM(quantity) as total_sold')
                ->selectRaw('SUM(subtotal) as revenue')
                ->whereHas('transaction', fn($q) => $q->where('status', 'completed'))
                ->groupBy('product_name')
                ->orderBy('total_sold', 'desc')
                ->limit(5)->get();

            $recentSales = PosTransaction::with('user')
                ->where('status', 'completed')
                ->orderBy('transaction_date', 'desc')
                ->limit(8)->get();

            $salesChartData = PosTransaction::selectRaw(
                "DATE(transaction_date) as date,
                SUM(total) as revenue,
                COUNT(*) as count"
            )->where('status', 'completed')
                ->where('transaction_date', '>=', now()->subDays(7))
                ->groupByRaw('DATE(transaction_date)')
                ->orderByRaw('DATE(transaction_date)')
                ->get();

            // Payment method breakdown (for doughnut chart)
            $paymentBreakdown = PosTransaction::where('status', 'completed')
                ->whereMonth('transaction_date', $now->month)
                ->whereYear('transaction_date', $now->year)
                ->selectRaw('payment_method, SUM(total) as total')
                ->groupBy('payment_method')
                ->pluck('total', 'payment_method')
                ->mapWithKeys(fn($v, $k) => [ucfirst(str_replace('_', ' ', $k)) => (float) $v]);

            // Profit estimate: sum of (price - cost) × qty for completed sales this month
            $profitEstimate = PosTransactionItem::whereHas('transaction', fn($q) =>
                $q->where('status', 'completed')
                  ->whereMonth('transaction_date', $now->month)
                  ->whereYear('transaction_date', $now->year)
            )->get()->sum(function ($item) {
                $cost = $item->product->cost ?? 0;
                return ($item->unit_price - $cost) * $item->quantity;
            });

            // Last month sales for comparison
            $lastMonthSales = PosTransaction::where('status', 'completed')
                ->whereMonth('transaction_date', $now->copy()->subMonth()->month)
                ->whereYear('transaction_date', $now->copy()->subMonth()->year)
                ->sum('total');

            // Top categories by revenue this month
            $topCategories = PosTransactionItem::whereHas('transaction', fn($q) =>
                $q->where('status', 'completed')
                  ->whereMonth('transaction_date', $now->month)
                  ->whereYear('transaction_date', $now->year)
            )->with('product.category')
                ->get()
                ->groupBy(fn($item) => $item->product->category->name ?? 'Other')
                ->map(fn($items) => $items->sum('subtotal'))
                ->sortDesc()
                ->take(5);
        }

        if ($role === 'admin') {
            $recentActivities = AuditLog::with('user')->orderBy('created_at', 'desc')->limit(8)->get();
        }

        if ($role === 'cashier') {
            $recentSales = PosTransaction::where('user_id', auth()->id())
                ->where('status', 'completed')
                ->whereDate('transaction_date', today())
                ->orderBy('transaction_date', 'desc')
                ->limit(10)->get();
        }

        return view('dashboard.index', compact(
            'stats', 'lowStockProducts', 'chartData', 'salesChartData',
            'recentActivities', 'topProducts', 'recentSales', 'recentTransactions',
            'categoryDistribution', 'paymentBreakdown', 'inventoryValue',
            'variantProductsCount', 'profitEstimate', 'lastMonthSales',
            'topCategories', 'lowStockEntries'
        ));
    }
}
