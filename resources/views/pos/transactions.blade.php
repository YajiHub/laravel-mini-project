@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
  <div class="max-w-7xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900"><i class="fas fa-receipt mr-2 text-blue-600"></i>Transaction History</h1>
        <p class="text-sm text-gray-500 mt-1">All POS transactions</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('reports.sales-csv', request()->query()) }}" class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
          <i class="fas fa-file-csv text-green-600"></i>Export CSV
        </a>
        <a href="{{ route('reports.sales-pdf', request()->query()) }}" class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
          <i class="fas fa-file-pdf text-red-600"></i>Export PDF
        </a>
        @if(auth()->user()->role->name === 'admin' || auth()->user()->role->name === 'store_manager')
        <a href="{{ route('settings.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-semibold hover:bg-gray-800 transition">
          <i class="fas fa-cog"></i>Store Settings
        </a>
        @endif
      </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
      <form method="GET" action="{{ route('pos.transactions') }}" class="flex flex-wrap gap-4 items-end">
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">From Date</label>
          <input type="date" name="from_date" value="{{ request('from_date') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">To Date</label>
          <input type="date" name="to_date" value="{{ request('to_date') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Payment</label>
          <select name="payment_method" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Methods</option>
            <option value="cash" @selected(request('payment_method')==='cash')>Cash</option>
            <option value="card" @selected(request('payment_method')==='card')>Card</option>
            <option value="check" @selected(request('payment_method')==='check')>Check</option>
            <option value="bank_transfer" @selected(request('payment_method')==='bank_transfer')>Bank Transfer</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status</label>
          <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Status</option>
            <option value="completed" @selected(request('status')==='completed')>Completed</option>
            <option value="voided" @selected(request('status')==='voided')>Voided</option>
          </select>
        </div>
        <div class="flex gap-2">
          <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition"><i class="fas fa-filter mr-1"></i>Filter</button>
          <a href="{{ route('pos.transactions') }}" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold text-sm hover:bg-gray-300 transition"><i class="fas fa-redo mr-1"></i>Reset</a>
        </div>
      </form>
    </div>

    {{-- Summary Cards --}}
    @php
      $cur = $settings['currency_symbol'] ?? '₱';
      $completed = $transactions->getCollection()->where('status','completed');
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs font-semibold text-gray-400 uppercase">Transactions</p>
        <p class="text-2xl font-extrabold text-gray-800 mt-1">{{ $transactions->total() }}</p>
      </div>
      <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs font-semibold text-gray-400 uppercase">Total Sales</p>
        <p class="text-2xl font-extrabold text-green-600 mt-1">{{ $cur }}{{ number_format($completed->sum('total'),2) }}</p>
      </div>
      <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs font-semibold text-gray-400 uppercase">Total Discounts</p>
        <p class="text-2xl font-extrabold text-red-500 mt-1">{{ $cur }}{{ number_format($completed->sum('discount'),2) }}</p>
      </div>
      <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs font-semibold text-gray-400 uppercase">Avg. Transaction</p>
        <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $cur }}{{ $completed->count() ? number_format($completed->avg('total'),2) : '0.00' }}</p>
      </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      @if($transactions->count())
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Receipt #</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date & Time</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cashier</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
              <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Items</th>
              <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Subtotal</th>
              <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Discount</th>
              <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Payment</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            @foreach($transactions as $t)
            <tr class="hover:bg-gray-50 {{ $t->status==='voided'?'opacity-60':'' }}">
              <td class="px-5 py-3 font-mono font-semibold text-gray-800 text-xs">{{ $t->transaction_number }}</td>
              <td class="px-5 py-3 text-gray-600 whitespace-nowrap">{{ $t->transaction_date?->format('M d, Y g:i A') }}</td>
              <td class="px-5 py-3 text-gray-800">{{ $t->user?->name }}</td>
              <td class="px-5 py-3 text-gray-600">{{ $t->customer_name ?: '—' }}</td>
              <td class="px-5 py-3 text-center text-gray-600">{{ $t->items->count() }}</td>
              <td class="px-5 py-3 text-right text-gray-800">{{ $cur }}{{ number_format($t->subtotal,2) }}</td>
              <td class="px-5 py-3 text-right {{ $t->discount>0?'text-red-500 font-medium':'text-gray-400' }}">
                {{ $t->discount>0?'−'.$cur.number_format($t->discount,2):'—' }}
              </td>
              <td class="px-5 py-3 text-right font-bold text-gray-800">{{ $cur }}{{ number_format($t->total,2) }}</td>
              <td class="px-5 py-3">
                @php $pm = $t->payment_method; $colors=['cash'=>'green','card'=>'blue','check'=>'yellow','bank_transfer'=>'purple']; $c=$colors[$pm]??'gray'; @endphp
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $c }}-100 text-{{ $c }}-800">
                  {{ ucwords(str_replace('_',' ',$pm)) }}
                </span>
              </td>
              <td class="px-5 py-3">
                @if($t->status==='voided')
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700"><i class="fas fa-ban mr-1"></i>Voided</span>
                @else
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700"><i class="fas fa-check mr-1"></i>Completed</span>
                @endif
              </td>
              <td class="px-5 py-3">
                <div class="flex items-center gap-2">
                  <a href="{{ route('pos.receipt', $t) }}" class="text-blue-600 hover:text-blue-800 text-xs font-semibold"><i class="fas fa-eye mr-1"></i>View</a>
                  <a href="{{ route('pos.receipt-pdf', $t) }}" target="_blank" class="text-gray-500 hover:text-gray-700 text-xs"><i class="fas fa-file-pdf"></i></a>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="px-5 py-4 border-t border-gray-100">
        {{ $transactions->links() }}
      </div>
      @else
      <div class="text-center py-16">
        <i class="fas fa-inbox text-4xl text-gray-300 mb-3 block"></i>
        <p class="text-gray-500 font-medium">No transactions found</p>
        <p class="text-gray-400 text-sm mt-1">Try adjusting your filters</p>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
