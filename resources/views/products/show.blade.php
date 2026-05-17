@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-900">← Back to Products</a>
        </div>

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
                    <p class="mt-2 text-sm text-gray-500">SKU: {{ $product->sku }}</p>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('products.edit', $product) }}" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                        Edit
                    </a>
                    <button type="button" onclick="document.getElementById('delete-modal').style.display='flex'" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Product Details</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $product->category->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Supplier</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('suppliers.show', $product->supplier) }}" class="text-blue-600 hover:text-blue-900">
                                {{ $product->supplier->name }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $product->description ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Pricing & Inventory</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Selling Price</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">₱{{ number_format($product->price, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Unit Cost</dt>
                        <dd class="mt-1 text-sm text-gray-900">₱{{ number_format($product->cost, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Profit Margin</dt>
                        <dd class="mt-1 text-sm text-gray-900">₱{{ number_format($product->price - $product->cost, 2) }} ({{ $product->cost > 0 ? round((($product->price - $product->cost) / $product->cost) * 100, 1) : 0 }}%)</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Current Quantity</dt>
                        <dd class="mt-1">
                            <span class="px-3 py-1 inline-flex text-lg font-semibold rounded-full
                                @if($product->quantity <= $product->low_stock_threshold)
                                    bg-red-100 text-red-800
                                @elseif($product->quantity == 0)
                                    bg-gray-100 text-gray-800
                                @else
                                    bg-green-100 text-green-800
                                @endif
                            ">
                                {{ $product->quantity }} units
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Low Stock Threshold</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $product->low_stock_threshold }} units</dd>
                    </div>
                </dl>
            </div>
        </div>

        @if($product->variants->count() > 0)
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Product Variants</h2>
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price Modifier</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($product->variants as $variant)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $variant->type }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $variant->value }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $variant->sku }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $variant->quantity }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $variant->price_modifier > 0 ? '+' : '' }}₱{{ number_format($variant->price_modifier, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($product->stockTransactions->count() > 0)
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Stock Transactions</h2>
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($product->stockTransactions->take(10) as $transaction)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $transaction->type === 'stock_in' ? 'bg-green-100 text-green-800' : ($transaction->type === 'stock_out' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}
                                        ">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction->quantity }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction->reference ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction->user->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No transactions yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="delete-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:50;align-items:center;justify-content:center;">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-red-700"><i class="fas fa-exclamation-triangle mr-2"></i>Confirm Deletion</h3>
            <button onclick="document.getElementById('delete-modal').style.display='none'" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <p class="text-sm text-gray-600 mb-4">This will permanently delete <strong>{{ $product->name }}</strong> ({{ $product->sku }}). This action cannot be undone.</p>

        <form method="POST" action="{{ route('products.destroy', $product) }}" class="space-y-4">
            @csrf
            @method('DELETE')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Enter your password to confirm</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="Your account password">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg text-sm transition">
                    <i class="fas fa-trash mr-2"></i>Delete Product
                </button>
                <button type="button" onclick="document.getElementById('delete-modal').style.display='none'" class="flex-1 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg text-sm transition">Cancel</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('delete-modal').addEventListener('click', function(e){
    if(e.target===this) this.style.display='none';
});
</script>
@endsection
