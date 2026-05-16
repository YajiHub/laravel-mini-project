@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-check-circle text-green-600 mr-2"></i>Transaction Complete
            </h1>
            <p class="mt-2 text-sm text-gray-600">Receipt #{{ $transaction->id }}</p>
        </div>

        <!-- Receipt Card -->
        <div class="bg-white shadow rounded-lg p-8">
            <!-- Store Info -->
            <div class="text-center mb-8 pb-8 border-b">
                <h2 class="text-2xl font-bold text-gray-900">QueenBuilders IMS</h2>
                <p class="text-sm text-gray-600">Hardware & Construction Supplies</p>
                <p class="text-xs text-gray-500 mt-2">{{ config('app.url') }}</p>
            </div>

            <!-- Transaction Info -->
            <div class="grid grid-cols-2 gap-4 mb-8 pb-8 border-b">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Date & Time</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $transaction->transaction_date->format('M d, Y H:i A') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Receipt Number</p>
                    <p class="text-sm font-semibold text-gray-900">#{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Cashier</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $transaction->user->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Payment Method</p>
                    <p class="text-sm font-semibold text-gray-900 capitalize">{{ $transaction->payment_method }}</p>
                </div>
                @if($transaction->customer_name)
                    <div class="col-span-2">
                        <p class="text-xs font-medium text-gray-500 uppercase">Customer</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $transaction->customer_name }}</p>
                    </div>
                @endif
            </div>

            <!-- Items Table -->
            <div class="mb-8 pb-8 border-b">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="text-right py-2 text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="text-right py-2 text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="text-right py-2 text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaction->items as $item)
                            <tr class="border-b">
                                <td class="py-3">
                                    <p class="font-medium text-gray-900">{{ $item->product->name }}</p>
                                    @if($item->variant)
                                        <p class="text-xs text-gray-600">{{ $item->variant->type }}: {{ $item->variant->value }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500">SKU: {{ $item->product->sku }}</p>
                                </td>
                                <td class="py-3 text-right text-gray-900">{{ $item->quantity }}</td>
                                <td class="py-3 text-right text-gray-900">₱{{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-3 text-right font-medium text-gray-900">₱{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="mb-8 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="font-medium text-gray-900">₱{{ number_format($transaction->subtotal, 2) }}</span>
                </div>
                @if($transaction->discount_amount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Discount:</span>
                        <span class="font-medium text-red-600">-₱{{ number_format($transaction->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-lg font-bold border-t pt-2">
                    <span class="text-gray-900">Total Amount:</span>
                    <span class="text-green-600">₱{{ number_format($transaction->total_amount, 2) }}</span>
                </div>
            </div>

            <!-- Notes -->
            @if($transaction->notes)
                <div class="mb-8 pb-8 border-t pt-8">
                    <p class="text-xs font-medium text-gray-500 uppercase mb-2">Notes</p>
                    <p class="text-sm text-gray-700">{{ $transaction->notes }}</p>
                </div>
            @endif

            <!-- Footer -->
            <div class="text-center py-8 border-t">
                <p class="text-xs text-gray-600">Thank you for your purchase!</p>
                <p class="text-xs text-gray-500 mt-1">For questions, please contact our customer service</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex gap-4 justify-center">
            <a href="{{ route('pos.receipt-pdf', $transaction) }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                <i class="fas fa-file-pdf mr-2"></i>Download PDF
            </a>
            <a href="{{ route('pos.index') }}" class="inline-flex items-center px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold rounded-lg transition">
                <i class="fas fa-plus-circle mr-2"></i>New Transaction
            </a>
        </div>
    </div>
</div>
@endsection
