<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\StockTransaction;
use App\Models\User;
use App\Models\PosTransaction;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get key statistics
        $stats = [
            'total_products' => Product::active()->count(),
            'low_stock_count' => Product::active()->lowStock()->count(),
            'total_categories' => Category::active()->count(),
            'total_suppliers' => Supplier::active()->count(),
            'out_of_stock' => Product::active()->where('quantity', 0)->count(),
            'total_users' => User::where('is_active', true)->count(),
            'total_sales_today' => PosTransaction::where('status', 'completed')
                ->whereDate('created_at', today())
                ->sum('total'),
        ];

        // Recent transactions
        $recentTransactions = StockTransaction::with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)->get();

        // Low stock products
        $lowStockProducts = Product::with('category')
            ->active()
            ->lowStock()
            ->orderBy('quantity')
            ->limit(8)->get();

        // Stock transactions last 7 days
        $chartData = StockTransaction::selectRaw(
            'DATE(created_at) as date,
            COUNT(CASE WHEN type = \'in\' THEN 1 END) as stock_in,
            COUNT(CASE WHEN type = \'out\' THEN 1 END) as stock_out'
        )->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recent audit logs
        $recentActivities = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Top selling products
        $topProducts = \App\Models\PosTransactionItem::select('product_name')
            ->selectRaw('SUM(quantity) as total_sold')
            ->selectRaw('SUM(subtotal) as revenue')
            ->groupBy('product_name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'recentTransactions',
            'lowStockProducts',
            'chartData',
            'recentActivities',
            'topProducts'
        ));
    }
}

