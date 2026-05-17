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
        $role = auth()->user()->role->name;

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
                                        ->whereMonth('transaction_date', now()->month)
                                        ->whereYear('transaction_date', now()->year)
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

        if ($role === 'inventory_manager') {
            $lowStockProducts = Product::with('category')->active()->lowStock()->orderBy('quantity')->limit(8)->get();

            $chartData = StockTransaction::selectRaw(
                'DATE(created_at) as date,
                SUM(CASE WHEN type = \'stock_in\' THEN quantity ELSE 0 END) as stock_in,
                SUM(CASE WHEN type = \'stock_out\' THEN quantity ELSE 0 END) as stock_out'
            )->where('created_at', '>=', now()->subDays(7))
                ->groupByRaw('DATE(created_at)')
                ->orderByRaw('DATE(created_at)')
                ->get();
        }

        if ($role === 'store_manager') {
            $topProducts = \App\Models\PosTransactionItem::select('product_name')
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
                'DATE(transaction_date) as date,
                SUM(total) as revenue,
                COUNT(*) as count'
            )->where('status', 'completed')
                ->where('transaction_date', '>=', now()->subDays(7))
                ->groupByRaw('DATE(transaction_date)')
                ->orderByRaw('DATE(transaction_date)')
                ->get();
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
            'recentActivities', 'topProducts', 'recentSales', 'recentTransactions'
        ));
    }
}
