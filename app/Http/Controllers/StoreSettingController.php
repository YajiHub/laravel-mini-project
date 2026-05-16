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
}
