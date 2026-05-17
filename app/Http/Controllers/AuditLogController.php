<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('model_type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Model type filter
        if ($modelType = $request->input('model_type')) {
            $query->where('model_type', $modelType);
        }

        // Action filter
        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        // Date range filter
        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate = $request->input('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $logs = $query->latest('created_at')->paginate(20)->withQueryString();

        // CSV Export
        if ($request->input('export') === 'csv') {
            $allLogs = $query->latest('created_at')->get();
            $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="audit-logs-' . now()->format('Y-m-d') . '.csv"'];
            $callback = function () use ($allLogs) {
                $f = fopen('php://output', 'w');
                fputcsv($f, ['ID', 'Timestamp', 'User', 'Action', 'Module', 'Record ID', 'Description', 'IP Address']);
                foreach ($allLogs as $log) {
                    fputcsv($f, [
                        $log->id,
                        $log->created_at->format('Y-m-d H:i:s'),
                        $log->user?->name ?? 'System',
                        $log->action,
                        $log->model_type,
                        $log->model_id,
                        $log->description,
                        $log->ip_address,
                    ]);
                }
                fclose($f);
            };
            return response()->stream($callback, 200, $headers);
        }

        $modelTypes = AuditLog::distinct('model_type')
                              ->pluck('model_type')
                              ->sort()
                              ->values();

        $actions = AuditLog::distinct('action')
                           ->pluck('action')
                           ->sort()
                           ->values();

        return view('admin.audit-logs.index', compact('logs', 'modelTypes', 'actions'));
    }

    /**
     * Display the specified audit log with detailed information.
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        return view('admin.audit-logs.show', compact('auditLog'));
    }
}

