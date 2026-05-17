<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PosTransaction;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Products inventory — PDF report.
     */
    public function inventoryPdf()
    {
        $products = Product::with(['category', 'supplier'])
            ->orderBy('category_id')
            ->get();

        $pdf = Pdf::loadView('reports.inventory-pdf', [
            'products'     => $products,
            'generated_at' => now()->format('F d, Y h:i A'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('inventory-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Products inventory — CSV export.
     */
    public function inventoryCsv()
    {
        $products = Product::with(['category', 'supplier'])->orderBy('name')->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($products) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['SKU', 'Product Name', 'Category', 'Supplier', 'Unit Price', 'Quantity', 'Unit', 'Low Stock Threshold', 'Status']);
            foreach ($products as $p) {
                fputcsv($f, [
                    $p->sku,
                    $p->name,
                    $p->category?->name ?? '—',
                    $p->supplier?->name ?? '—',
                    number_format($p->price, 2),
                    $p->quantity,
                    $p->unit,
                    $p->low_stock_threshold,
                    $p->is_active ? 'Active' : 'Inactive',
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * POS Sales transactions — CSV export.
     */
    public function salesCsv(Request $request)
    {
        $query = PosTransaction::with('user')
            ->orderBy('transaction_date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['completed', 'voided']);
        }

        // Default to current month if no dates specified
        $from = $request->input('from_date', now()->startOfMonth()->toDateString());
        $to   = $request->input('to_date', now()->toDateString());

        if ($request->filled('from_date') || ! $request->filled('to_date')) {
            $query->whereDate('transaction_date', '>=', $from);
        }
        if ($request->filled('to_date') || ! $request->filled('from_date')) {
            $query->whereDate('transaction_date', '<=', $to);
        }

        $transactions = $query->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($transactions) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['Transaction #', 'Date', 'Cashier', 'Customer', 'Subtotal', 'Discount', 'Tax', 'Total', 'Cash Tendered', 'Change', 'Status']);
            foreach ($transactions as $t) {
                fputcsv($f, [
                    $t->transaction_number,
                    $t->transaction_date?->format('Y-m-d H:i'),
                    $t->user?->name ?? '—',
                    $t->customer_name ?? 'Walk-in',
                    number_format($t->subtotal, 2),
                    number_format($t->discount, 2),
                    number_format($t->tax_amount, 2),
                    number_format($t->total, 2),
                    number_format($t->cash_tendered ?? 0, 2),
                    number_format($t->change_amount ?? 0, 2),
                    $t->status,
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Sales Summary PDF — for store manager reporting.
     */
    public function salesSummaryPdf(Request $request)
    {
        $hasDateFilter = $request->filled('from_date') || $request->filled('to_date');
        $from = $request->from_date ?? ($hasDateFilter ? null : null);
        $to   = $request->to_date   ?? ($hasDateFilter ? null : null);

        $query = PosTransaction::with('user')
            ->orderBy('transaction_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['completed', 'voided']);
        }

        if ($hasDateFilter) {
            if ($request->filled('from_date')) {
                $query->whereDate('transaction_date', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('transaction_date', '<=', $request->to_date);
            }
        }

        $transactions = $query->get();

        $totalRevenue  = $transactions->sum('total');
        $totalTax      = $transactions->sum('tax_amount');
        $totalDiscount = $transactions->sum('discount');

        $pdf = Pdf::loadView('reports.sales-pdf', [
            'transactions'  => $transactions,
            'totalRevenue'  => $totalRevenue,
            'totalTax'      => $totalTax,
            'totalDiscount' => $totalDiscount,
            'from'          => $from,
            'to'            => $to,
            'generated_at'  => now()->format('F d, Y h:i A'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('sales-report-' . date('Y-m-d') . '.pdf');
    }
}
