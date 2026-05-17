<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Barryvdh\DomPDF\Facade\Pdf;

class SystemController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function sqlDump()
    {
        try {
            $db = config('database.connections.pgsql.database');
            $user = config('database.connections.pgsql.username');
            $host = config('database.connections.pgsql.host');
            $port = config('database.connections.pgsql.port');
            $password = config('database.connections.pgsql.password');

            $filename = 'queenbuilders-ims-full-' . date('Y-m-d-His') . '.sql';
            $tempPath = storage_path('app/' . $filename);

            $command = sprintf(
                'set PGPASSWORD=%s && pg_dump -h %s -p %s -U %s -d %s --no-owner --no-acl > "%s"',
                $password, $host, $port, $user, $db, $tempPath
            );

            exec($command, $output, $exitCode);

            if ($exitCode !== 0 || !file_exists($tempPath)) {
                // Fallback: use schema dump only
                Artisan::call('schema:dump', ['--path' => $tempPath]);
            }

            if (!file_exists($tempPath)) {
                return back()->with('error', 'Database dump failed. Check pg_dump availability.');
            }

            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'export_sql',
                'model_type' => 'System',
                'description' => 'Full SQL dump downloaded',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->download($tempPath, $filename)->deleteFileAfterSend();
        } catch (\Exception $e) {
            return back()->with('error', 'Dump failed: ' . $e->getMessage());
        }
    }

    public function schemaDump()
    {
        try {
            $filename = 'queenbuilders-schema-' . date('Y-m-d-His') . '.sql';
            $tempPath = storage_path('app/' . $filename);

            Artisan::call('schema:dump', ['--path' => $tempPath]);

            if (!file_exists($tempPath)) {
                return back()->with('error', 'Schema dump failed.');
            }

            return response()->download($tempPath, $filename)->deleteFileAfterSend();
        } catch (\Exception $e) {
            return back()->with('error', 'Schema dump failed: ' . $e->getMessage());
        }
    }

    public function userManualPdf()
    {
        $html = file_get_contents(base_path('docs/USER-MANUAL.html'));
        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');

        return $pdf->download('QueenBuilders-IMS-User-Manual.pdf');
    }

    public function techDocsPdf()
    {
        $html = file_get_contents(base_path('docs/TECHNICAL-DOCS.html'));
        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');

        return $pdf->download('QueenBuilders-IMS-Technical-Documentation.pdf');
    }
}
