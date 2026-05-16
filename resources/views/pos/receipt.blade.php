@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-8 px-4">
  <div class="max-w-2xl mx-auto">

    {{-- Actions --}}
    <div class="flex gap-3 mb-6 justify-center">
      <a href="{{ route('pos.receipt-pdf', $transaction) }}" target="_blank"
         class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition">
        <i class="fas fa-file-pdf"></i>Download PDF
      </a>
      <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-700 hover:bg-gray-800 text-white font-semibold rounded-lg text-sm transition">
        <i class="fas fa-print"></i>Print
      </button>
      <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg text-sm transition">
        <i class="fas fa-plus-circle"></i>New Sale
      </a>
    </div>

    {{-- Receipt --}}
    <div class="bg-white shadow-lg rounded-2xl overflow-hidden" id="receipt-card">
      {{-- Green header --}}
      <div class="bg-green-600 text-white text-center py-6 px-8">
        <div class="text-4xl mb-2"><i class="fas fa-check-circle"></i></div>
        <h1 class="text-xl font-bold">Transaction Complete</h1>
        <p class="text-green-200 text-sm mt-1">{{ $settings['store_name'] ?? 'QueenBuilders Hardware' }}</p>
      </div>

      <div class="p-8">
        {{-- Store info --}}
        <div class="text-center mb-6 pb-6 border-b border-dashed border-gray-200">
          <h2 class="text-2xl font-extrabold text-gray-800">{{ $settings['store_name'] ?? 'QueenBuilders Hardware' }}</h2>
          <p class="text-sm text-gray-500 mt-1">{{ $settings['store_address'] ?? 'Hardware & Construction Supplies' }}</p>
        </div>

        {{-- Transaction meta --}}
        <div class="grid grid-cols-2 gap-x-6 gap-y-3 mb-6 pb-6 border-b border-gray-100">
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Receipt No.</p>
            <p class="text-sm font-bold text-gray-800 mt-0.5">{{ $transaction->transaction_number }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Date & Time</p>
            <p class="text-sm font-bold text-gray-800 mt-0.5">{{ $transaction->transaction_date->format('M d, Y g:i A') }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Cashier</p>
            <p class="text-sm font-bold text-gray-800 mt-0.5">{{ $transaction->user->name }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Payment</p>
            <p class="text-sm font-bold text-gray-800 mt-0.5 capitalize">{{ str_replace('_',' ',$transaction->payment_method) }}</p>
          </div>
          @if($transaction->customer_name)
          <div class="col-span-2">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Customer</p>
            <p class="text-sm font-bold text-gray-800 mt-0.5">{{ $transaction->customer_name }}</p>
          </div>
          @endif
          @if($transaction->reference_number)
          <div class="col-span-2">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Reference No.</p>
            <p class="text-sm font-bold text-gray-800 mt-0.5">{{ $transaction->reference_number }}</p>
          </div>
          @endif
          @if($transaction->status === 'voided')
          <div class="col-span-2">
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">
              <i class="fas fa-ban"></i> VOIDED
            </span>
          </div>
          @endif
        </div>

        {{-- Items --}}
        <table class="w-full text-sm mb-6">
          <thead>
            <tr class="border-b-2 border-gray-200">
              <th class="text-left py-2 text-xs font-semibold text-gray-500 uppercase">Item</th>
              <th class="text-center py-2 text-xs font-semibold text-gray-500 uppercase">Qty</th>
              <th class="text-right py-2 text-xs font-semibold text-gray-500 uppercase">Price</th>
              <th class="text-right py-2 text-xs font-semibold text-gray-500 uppercase">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            @foreach($transaction->items as $item)
            <tr>
              <td class="py-3">
                <p class="font-semibold text-gray-800">{{ $item->product_name }}</p>
                @if($item->variant)
                  <p class="text-xs text-gray-500">{{ $item->variant->type }}: {{ $item->variant->value }}</p>
                @endif
                <p class="text-xs text-gray-400">SKU: {{ $item->sku }}</p>
              </td>
              <td class="py-3 text-center text-gray-700">{{ $item->quantity }}</td>
              <td class="py-3 text-right text-gray-700">{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($item->unit_price,2) }}</td>
              <td class="py-3 text-right font-semibold text-gray-800">{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($item->subtotal,2) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>

        {{-- Totals --}}
        <div class="border-t-2 border-dashed border-gray-200 pt-4 space-y-2">
          <div class="flex justify-between text-sm text-gray-600">
            <span>Subtotal</span>
            <span>{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->subtotal,2) }}</span>
          </div>
          @if($transaction->discount > 0)
          <div class="flex justify-between text-sm text-red-600">
            <span>Discount</span>
            <span>−{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->discount,2) }}</span>
          </div>
          @endif
          @if($transaction->tax_amount > 0)
          <div class="flex justify-between text-sm text-gray-600">
            <span>{{ $settings['tax_label'] ?? 'VAT' }} ({{ number_format($transaction->tax_rate,2) }}%)</span>
            <span>{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->tax_amount,2) }}</span>
          </div>
          @endif
          <div class="flex justify-between text-xl font-extrabold text-gray-900 border-t-2 border-gray-200 pt-3 mt-2">
            <span>TOTAL</span>
            <span class="text-green-600">{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->total,2) }}</span>
          </div>
          @if($transaction->payment_method === 'cash')
          <div class="flex justify-between text-sm text-gray-600">
            <span>Cash Tendered</span>
            <span>{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->cash_tendered,2) }}</span>
          </div>
          <div class="flex justify-between text-sm font-bold text-green-700 bg-green-50 rounded-lg px-3 py-2">
            <span>Change</span>
            <span>{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->change_amount,2) }}</span>
          </div>
          @endif
        </div>

        {{-- Footer --}}
        <div class="text-center mt-8 pt-6 border-t border-dashed border-gray-200">
          <p class="text-sm font-medium text-gray-600">{{ $settings['receipt_footer'] ?? 'Thank you for your purchase!' }}</p>
          <p class="text-xs text-gray-400 mt-1">For concerns, please contact our store.</p>
        </div>
      </div>
    </div>

    @if($transaction->status !== 'voided' && (auth()->user()->role->name === 'admin' || auth()->user()->role->name === 'store_manager'))
    <div class="mt-4 text-center">
      <button onclick="voidTransaction({{ $transaction->id }})"
        class="text-sm text-red-500 hover:text-red-700 underline">
        <i class="fas fa-ban mr-1"></i>Void this transaction
      </button>
    </div>
    @endif
  </div>
</div>

<style>
@media print {
  nav, .no-print, a[href] { display: none !important; }
  body { background: white; }
  #receipt-card { box-shadow: none; }
}
</style>

<script>
async function voidTransaction(id) {
  if (!confirm('Void this transaction? Stock will be restored.')) return;
  const r = await fetch('/pos/'+id+'/void', {
    method:'POST',
    headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}
  }).then(x=>x.json());
  if (r.success) location.reload();
  else alert(r.message);
}
</script>
@endsection
