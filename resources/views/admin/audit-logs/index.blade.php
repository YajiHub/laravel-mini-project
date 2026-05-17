@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
  <div class="max-w-7xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900"><i class="fas fa-clipboard-list mr-2 text-yellow-600"></i>Audit Logs</h1>
        <p class="text-sm text-gray-500 mt-1">All system actions are tracked here. Admin view only.</p>
      </div>
      <a href="{{ route('admin.audit-logs.index', array_merge(request()->query(), ['export' => 'csv'])) }}"
         class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg text-sm transition">
        <i class="fas fa-file-csv"></i>Export CSV
      </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
      <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="flex flex-wrap gap-4 items-end">
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Search</label>
          <input type="text" name="search" value="{{ request('search') }}" placeholder="Action, model, description..."
            class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-52">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Module</label>
          <select name="model_type" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Modules</option>
            @foreach($modelTypes as $mt)
              <option value="{{ $mt }}" @selected(request('model_type') === $mt)>{{ $mt }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Action</label>
          <select name="action" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Actions</option>
            @foreach($actions as $act)
              <option value="{{ $act }}" @selected(request('action') === $act)>{{ ucfirst($act) }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">From</label>
          <input type="date" name="from_date" value="{{ request('from_date') }}"
            class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">To</label>
          <input type="date" name="to_date" value="{{ request('to_date') }}"
            class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex gap-2">
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition"><i class="fas fa-filter mr-1"></i>Filter</button>
          <a href="{{ route('admin.audit-logs.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold text-sm hover:bg-gray-300 transition"><i class="fas fa-redo mr-1"></i>Reset</a>
        </div>
      </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      @if($logs->count())
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Time</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Action</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Module</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">IP</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Detail</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            @foreach($logs as $log)
            @php
              $actionColors = [
                'created' => 'green',
                'updated' => 'blue',
                'deleted' => 'red',
                'login'   => 'indigo',
                'logout'  => 'gray',
              ];
              $color = $actionColors[$log->action] ?? 'yellow';
            @endphp
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $log->created_at->format('M d Y g:i A') }}</td>
              <td class="px-4 py-3 text-gray-700 font-medium text-xs">{{ $log->user?->name ?? '<span class="text-gray-400">System</span>' }}</td>
              <td class="px-4 py-3 text-center">
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-800">
                  {{ ucfirst($log->action) }}
                </span>
              </td>
              <td class="px-4 py-3 text-gray-600 text-xs font-mono">{{ $log->model_type }}</td>
              <td class="px-4 py-3 text-gray-500 text-xs max-w-xs truncate">{{ $log->description }}</td>
              <td class="px-4 py-3 text-gray-400 text-xs font-mono">{{ $log->ip_address }}</td>
              <td class="px-4 py-3 text-center">
                <a href="{{ route('admin.audit-logs.show', $log) }}" class="text-blue-500 hover:text-blue-700 text-xs"><i class="fas fa-eye"></i></a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="px-5 py-4 border-t border-gray-100">
        {{ $logs->withQueryString()->links() }}
      </div>
      @else
      <div class="text-center py-16">
        <i class="fas fa-clipboard text-4xl text-gray-300 mb-3 block"></i>
        <p class="text-gray-500 font-medium">No audit logs found</p>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
