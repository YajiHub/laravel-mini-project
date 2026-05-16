@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
  <div class="max-w-7xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900"><i class="fas fa-dolly mr-2 text-blue-600"></i>Stock Transactions</h1>
        <p class="text-sm text-gray-500 mt-1">View and record stock movements.</p>
      </div>
      <button onclick="document.getElementById('add-modal').classList.add('show')"
        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition">
        <i class="fas fa-plus"></i>Add Stock Movement
      </button>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3 text-green-800 text-sm font-medium">
      <i class="fas fa-check-circle text-green-600"></i>{{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
      <i class="fas fa-exclamation-triangle mr-2 text-red-500"></i>{{ $errors->first() }}
    </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
      <form method="GET" action="{{ route('stock-transactions.index') }}" class="flex flex-wrap gap-4 items-end">
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Product</label>
          <select name="product_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Products</option>
            @foreach($products as $p)
              <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>{{ $p->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Type</label>
          <select name="type" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Types</option>
            <option value="stock_in" @selected(request('type')==='stock_in')>Stock In</option>
            <option value="stock_out" @selected(request('type')==='stock_out')>Stock Out</option>
            <option value="adjustment" @selected(request('type')==='adjustment')>Adjustment</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">From Date</label>
          <input type="date" name="from_date" value="{{ request('from_date') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">To Date</label>
          <input type="date" name="to_date" value="{{ request('to_date') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex gap-2">
          <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition"><i class="fas fa-filter mr-1"></i>Filter</button>
          <a href="{{ route('stock-transactions.index') }}" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold text-sm hover:bg-gray-300 transition"><i class="fas fa-redo mr-1"></i>Reset</a>
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
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
              <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Type</th>
              <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Qty</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reference</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Notes</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">By</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            @foreach($transactions as $tx)
            <tr class="hover:bg-gray-50">
              <td class="px-5 py-3 text-gray-400 text-xs font-mono">{{ $tx->id }}</td>
              <td class="px-5 py-3 text-gray-600 whitespace-nowrap text-xs">{{ $tx->created_at->format('M d, Y g:i A') }}</td>
              <td class="px-5 py-3">
                <div class="font-medium text-gray-800">{{ $tx->product?->name ?? '—' }}</div>
                <div class="text-xs text-gray-400">{{ $tx->product?->sku }}</div>
              </td>
              <td class="px-5 py-3 text-center">
                @php
                  $typeMap = [
                    'stock_in'   => ['label' => 'Stock In',   'color' => 'green'],
                    'stock_out'  => ['label' => 'Stock Out',  'color' => 'red'],
                    'adjustment' => ['label' => 'Adjustment', 'color' => 'yellow'],
                  ];
                  $t = $typeMap[$tx->type] ?? ['label' => $tx->type, 'color' => 'gray'];
                @endphp
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $t['color'] }}-100 text-{{ $t['color'] }}-800">
                  {{ $t['label'] }}
                </span>
              </td>
              <td class="px-5 py-3 text-right font-bold {{ $tx->type === 'stock_out' ? 'text-red-600' : 'text-green-600' }}">
                {{ $tx->type === 'stock_out' ? '−' : '+' }}{{ $tx->quantity }}
              </td>
              <td class="px-5 py-3 text-gray-500 text-xs font-mono">{{ $tx->reference ?? '—' }}</td>
              <td class="px-5 py-3 text-gray-500 text-xs max-w-xs truncate">{{ $tx->notes ?? '—' }}</td>
              <td class="px-5 py-3 text-gray-600 text-xs">{{ $tx->user?->name ?? '—' }}</td>
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
        <p class="text-gray-500 font-medium">No stock transactions found</p>
        <p class="text-gray-400 text-sm mt-1">Adjust filters or record a new movement above.</p>
      </div>
      @endif
    </div>

  </div>
</div>

{{-- Add Stock Modal --}}
<div class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center" id="add-modal" style="display:none">
  <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-boxes mr-2 text-blue-600"></i>Record Stock Movement</h3>
      <button onclick="document.getElementById('add-modal').style.display='none'" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
    </div>
    <form method="POST" action="{{ route('stock-transactions.store') }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
        <select name="product_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Select product...</option>
          @foreach($products as $p)
            <option value="{{ $p->id }}">{{ $p->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Type *</label>
        <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="stock_in">Stock In (Receiving)</option>
          <option value="stock_out">Stock Out (Manual removal)</option>
          <option value="adjustment">Adjustment</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
        <input type="number" name="quantity" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. 50">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Optional notes..."></textarea>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="submit" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition">
          <i class="fas fa-save mr-2"></i>Save
        </button>
        <button type="button" onclick="document.getElementById('add-modal').style.display='none'"
          class="flex-1 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg text-sm transition">Cancel</button>
      </div>
    </form>
  </div>
</div>
<script>
document.getElementById('add-modal').addEventListener('click', function(e){
  if(e.target===this) this.style.display='none';
});
// Show modal if validation errors
@if($errors->any())
document.getElementById('add-modal').style.display='flex';
@endif
</script>
@endsection
