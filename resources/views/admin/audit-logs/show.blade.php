@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-search mr-2"></i>Audit Log Details
                </h1>
                <p class="mt-1 text-sm text-gray-600">Change details for record #{{ $auditLog->model_id }}</p>
            </div>
            <a href="{{ route('admin.audit-logs.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 font-medium transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>

        <!-- Summary Card -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Date & Time</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $auditLog->created_at->format('M d, Y H:i:s') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">User</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $auditLog->user?->name ?? 'System' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Action</p>
                    <p class="mt-1">
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                            @switch($auditLog->action)
                                @case('created')
                                    bg-green-100 text-green-800
                                    @break
                                @case('updated')
                                    bg-blue-100 text-blue-800
                                    @break
                                @case('deleted')
                                    bg-red-100 text-red-800
                                    @break
                                @default
                                    bg-gray-100 text-gray-800
                            @endswitch
                        ">
                            {{ ucfirst($auditLog->action) }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Model Type</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ class_basename($auditLog->model_type) }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Record ID</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900">#{{ $auditLog->model_id }}</p>
                </div>
            </div>
        </div>

        <!-- Description -->
        @if($auditLog->description)
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Description</h3>
                <p class="text-gray-700">{{ $auditLog->description }}</p>
            </div>
        @endif

        <!-- Changes -->
        @if($auditLog->old_values || $auditLog->new_values)
            <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-exchange-alt mr-2"></i>Changes
                    </h3>
                </div>

                <div class="p-6 space-y-6">
                    @php
                        $oldValues = json_decode($auditLog->old_values, true) ?? [];
                        $newValues = json_decode($auditLog->new_values, true) ?? [];
                        $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
                    @endphp

                    @foreach(array_sort($allKeys) as $key)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Old Value -->
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ str_replace('_', ' ', $key) }} (Before)</p>
                                <div class="bg-red-50 border border-red-200 rounded p-3">
                                    @if($oldValues[$key] ?? null)
                                        <code class="text-red-800 text-sm break-all">{{ json_encode($oldValues[$key], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code>
                                    @else
                                        <span class="text-gray-500 italic">No previous value</span>
                                    @endif
                                </div>
                            </div>

                            <!-- New Value -->
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ str_replace('_', ' ', $key) }} (After)</p>
                                <div class="bg-green-50 border border-green-200 rounded p-3">
                                    @if($newValues[$key] ?? null)
                                        <code class="text-green-800 text-sm break-all">{{ json_encode($newValues[$key], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code>
                                    @else
                                        <span class="text-gray-500 italic">No new value</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-gray-500">No change data recorded for this action</p>
            </div>
        @endif
    </div>
</div>

@php
    function array_sort($array) {
        sort($array);
        return $array;
    }
@endphp
@endsection
