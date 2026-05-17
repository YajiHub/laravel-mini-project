@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-cog mr-2"></i>Store Settings
            </h1>
            <p class="mt-1 text-sm text-gray-600">Configure tax rates, store information, and receipt settings.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                <i class="fas fa-check-circle text-green-600"></i>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            <div class="bg-white shadow rounded-xl overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-store mr-2 text-blue-600"></i>Store Information
                    </h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($settings as $setting)
                        <div class="px-6 py-4 flex items-start gap-6">
                            <div class="w-48 shrink-0 pt-1">
                                <label for="setting_{{ $setting->key }}" class="block text-sm font-medium text-gray-700">
                                    {{ $setting->label }}
                                </label>
                                @if($setting->description)
                                    <p class="text-xs text-gray-500 mt-1">{{ $setting->description }}</p>
                                @endif
                            </div>
                            <div class="flex-1">
                                @if($setting->type === 'number')
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="number"
                                            id="setting_{{ $setting->key }}"
                                            name="settings[{{ $setting->key }}]"
                                            value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                            step="0.01"
                                            min="0"
                                            max="{{ $setting->key === 'tax_rate' ? '100' : '' }}"
                                            class="w-32 px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                        >
                                        @if($setting->key === 'tax_rate')
                                            <span class="text-gray-500 text-sm">%</span>
                                        @endif
                                    </div>
                                @else
                                    <input
                                        type="text"
                                        id="setting_{{ $setting->key }}"
                                        name="settings[{{ $setting->key }}]"
                                        value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    >
                                @endif
                                @error('settings.' . $setting->key)
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg text-sm transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm transition">
                        <i class="fas fa-save mr-2"></i>Save Settings
                    </button>
                </div>
            </div>
        </form>

        {{-- Backup Section --}}
        <div class="mt-8 bg-white shadow rounded-xl overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-database mr-2 text-green-600"></i>Backup & Maintenance
                </h2>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-600">
                    Create a manual database backup. Automated backups run weekly on Sundays at 2:00 AM.
                    Backups are retained for 30 days.
                </p>
                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ route('backup.run') }}" onsubmit="return confirm('Start a manual database backup now?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-sm transition">
                            <i class="fas fa-download mr-2"></i>Backup Now
                        </button>
                    </form>
                    <span class="text-xs text-gray-400">Database backup (PostgreSQL) — stored in <code>storage/app/QueenBuilders-IMS/</code></span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
