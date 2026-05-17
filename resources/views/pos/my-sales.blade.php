@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
  <div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900"><i class="fas fa-receipt mr-2 text-blue-600"></i>My Sales History</h1>
        <p class="text-sm text-gray-500 mt-1">All transactions processed by you</p>
      </div>
      <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg text-sm transition">
        <i class="fas fa-cash-register"></i>Back to POS
      </a>
    </div>

    {{-- Today's Summary --}}
    <div class="grid grid-cols-2 gap-4">
      <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-1">
          <span class="text-xs font-semibold text-gray-400 uppercase">My Sales Today</span>
          <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center text-green-600"><i class="fas fa-peso-sign text-sm"></i></span>
        </div>
        <p class="text-2xl font-extrabold text-green-600">&#x20b1;{{ number_format($totalToday, 2) }}</p>
      </div>
      <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-1">
          <span class="text-xs font-semibold text-gray-400 uppercase">Transactions Today</span>
          <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600"><i class="fas fa-receipt text-sm"></i></span>
        </div>
        <p class="text-2xl font-extrabold text-gray-800">{{ $countToday }}</p>
      </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
      <form method="GET" action="{{ route('pos.my-sales') }}" class="flex flex-wrap gap-4 items-end">
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">From Date</label>
          <input type="date" name="from_date" value="{{ request('from_date') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">To Date</label>
          <input type="date" name="to_date" value="{{ request('to_date') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status</label>
          <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All</option>
            <option value="completed" @selected(request('status') === 'completed')>Completed</option>
            <option value="voided" @selected(request('status') === 'voided')>Voided</option>
          </select>
        </div>
        <div class="flex gap-2">
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition"><i class="fas fa-filter mr-1"></i>Filter</button>
          <a href="{{ route('pos.my-sales') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition"><i class="fas fa-redo mr-1"></i>Reset</a>
        </div>
      </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      @if($transactions->count())
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Transaction #</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date & Time</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
              <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Items</th>
              <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
              <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
              <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            @foreach($transactions as $t)
            <tr class="hover:bg-gray-50">
              <td class="px-5 py-3 font-mono text-xs text-gray-700">{{ $t->transaction_number }}</td>
              <td class="px-5 py-3 text-xs text-gray-600 whitespace-nowrap">{{ $t->transaction_date?->format('M d, Y g:i A') }}</td>
              <td class="px-5 py-3 text-xs text-gray-600">{{ $t->customer_name ?? 'Walk-in' }}</td>
              <td class="px-5 py-3 text-right text-xs text-gray-600">{{ $t->items->sum('quantity') }}</td>
              <td class="px-5 py-3 text-right font-bold text-gray-800">&#x20b1;{{ number_format($t->total, 2) }}</td>
              <td class="px-5 py-3 text-center">
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $t->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                  {{ ucfirst($t->status) }}
                </span>
              </td>
              <td class="px-5 py-3 text-center">
                <div class="flex items-center justify-center gap-2">
                  <a href="{{ route('pos.receipt', $t) }}" class="text-blue-500 hover:text-blue-700 text-xs font-medium"><i class="fas fa-eye mr-1"></i>View</a>
                  <a href="{{ route('pos.receipt-pdf', $t) }}" class="text-red-500 hover:text-red-700 text-xs font-medium"><i class="fas fa-file-pdf mr-1"></i>PDF</a>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="px-5 py-4 border-t border-gray-100">
        {{ $transactions->withQueryString()->links() }}
      </div>
      @else
      <div class="text-center py-16">
        <i class="fas fa-receipt text-4xl text-gray-300 mb-3 block"></i>
        <p class="text-gray-500 font-medium">No transactions found</p>
        <p class="text-gray-400 text-sm mt-1">Transactions you process will appear here.</p>
        <a href="{{ route('pos.index') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition">
          <i class="fas fa-cash-register"></i>Open POS Terminal
        </a>
      </div>
      @endif
    </div>

  </div>
</div>
@endsection
