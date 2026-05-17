@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
  <div class="max-w-4xl mx-auto space-y-5">

    <div class="flex items-center gap-3">
      <a href="{{ route('admin.audit-logs.index') }}" class="text-gray-400 hover:text-gray-600 text-sm"><i class="fas fa-arrow-left mr-1"></i>Back to Logs</a>
      <span class="text-gray-300">/</span>
      <span class="text-sm text-gray-600 font-medium">Log #{{ $auditLog->id }}</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
        <h1 class="text-base font-bold text-gray-800"><i class="fas fa-clipboard-check mr-2 text-yellow-600"></i>Audit Log Detail</h1>
        <span class="text-xs text-gray-400">{{ $auditLog->created_at->format('F d, Y g:i:s A') }}</span>
      </div>
      <div class="p-6 grid grid-cols-2 gap-5 text-sm">
        <div><span class="font-semibold text-gray-500">User</span><p class="text-gray-800 mt-1">{{ $auditLog->user?->name ?? 'System' }}</p></div>
        <div><span class="font-semibold text-gray-500">Action</span><p class="text-gray-800 mt-1">{{ ucfirst($auditLog->action) }}</p></div>
        <div><span class="font-semibold text-gray-500">Module</span><p class="font-mono text-gray-700 mt-1">{{ $auditLog->model_type }}</p></div>
        <div><span class="font-semibold text-gray-500">Record ID</span><p class="font-mono text-gray-700 mt-1">{{ $auditLog->model_id ?? '—' }}</p></div>
        <div><span class="font-semibold text-gray-500">IP Address</span><p class="font-mono text-gray-700 mt-1">{{ $auditLog->ip_address ?? '—' }}</p></div>
        <div class="col-span-2"><span class="font-semibold text-gray-500">Description</span><p class="text-gray-700 mt-1">{{ $auditLog->description ?? '—' }}</p></div>
      </div>

      @if($auditLog->old_values || $auditLog->new_values)
      <div class="border-t border-gray-100 p-6 grid grid-cols-2 gap-5">
        @if($auditLog->old_values)
        <div>
          <p class="text-xs font-semibold text-red-600 uppercase mb-2">Before</p>
          <pre class="bg-red-50 rounded-lg p-3 text-xs text-red-800 overflow-x-auto">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif
        @if($auditLog->new_values)
        <div>
          <p class="text-xs font-semibold text-green-600 uppercase mb-2">After</p>
          <pre class="bg-green-50 rounded-lg p-3 text-xs text-green-800 overflow-x-auto">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
