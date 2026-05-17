<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use Illuminate\Http\Request;

class StoreSettingController extends Controller
{
    public function index()
    {
        $settings = StoreSetting::query()->orderBy('id')->get();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings'       => 'required|array',
            'settings.*'     => 'nullable|string|max:500',
        ]);

        foreach ($data['settings'] as $key => $value) {
            StoreSetting::where('key', $key)->update(['value' => $value]);
        }

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    public function backup()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('backup:run --only-db', []);
            $output = \Illuminate\Support\Facades\Artisan::output();

            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'backup',
                'model_type' => 'System',
                'description' => 'Manual database backup triggered',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Backup completed successfully.');
        } catch (\Exception $e) {
            \Log::error('Manual backup failed: ' . $e->getMessage());
            return back()->with('error', 'Backup failed. Check logs for details.');
        }
    }
}
