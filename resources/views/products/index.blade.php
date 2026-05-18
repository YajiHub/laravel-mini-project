@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><i class="fas fa-boxes mr-2 text-blue-600"></i>Products</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage your inventory catalogue</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('products.export.excel') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                        <i class="fas fa-file-excel mr-2 text-green-700"></i>Excel
                    </a>
                    <a href="{{ route('products.import') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                        <i class="fas fa-file-import mr-2 text-purple-600"></i>Import
                    </a>
                    <a href="{{ route('reports.inventory-csv') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                        <i class="fas fa-file-csv mr-2 text-green-600"></i>Export CSV
                    </a>
                    <a href="{{ route('reports.inventory-pdf') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                        <i class="fas fa-file-pdf mr-2 text-red-600"></i>Export PDF
                    </a>
                    <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>Add Product
                    </a>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                <div class="text-red-800">
                    <strong>Error:</strong>
                    <ul class="mt-2 ml-4 list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-6 bg-white shadow rounded-lg p-6">
            <form method="GET" action="{{ route('products.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Product name or SKU..." class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                        <select name="supplier" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex gap-2">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium text-sm transition">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        <a href="{{ route('products.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 font-medium text-sm transition">
                            <i class="fas fa-redo mr-2"></i>Reset
                        </a>
                    </div>
                    <div class="text-sm text-gray-600">
                        Showing <strong>{{ $products->total() }}</strong> products
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($products->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-14">Image</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($products as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover border border-gray-200">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                                                <i class="fas fa-box text-sm"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->sku }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->category->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->supplier->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($product->quantity == 0)
                                                bg-gray-100 text-gray-800
                                            @elseif($product->quantity <= $product->low_stock_threshold)
                                                bg-red-100 text-red-800
                                            @else
                                                bg-green-100 text-green-800
                                            @endif
                                        ">
                                            {{ $product->quantity }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($product->price, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('products.show', $product) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                        <a href="{{ route('products.edit', $product) }}" class="ml-4 text-yellow-600 hover:text-yellow-900">Edit</a>
                                        <button onclick="document.getElementById('delete-modal-{{ $product->id }}').style.display='flex'" class="ml-4 text-red-600 hover:text-red-900">Delete</button>

                                        {{-- Delete Modal per product --}}
                                        <div id="delete-modal-{{ $product->id }}" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:50;align-items:center;justify-content:center;">
                                            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h3 class="text-lg font-bold text-red-700"><i class="fas fa-exclamation-triangle mr-2"></i>Confirm Deletion</h3>
                                                    <button onclick="document.getElementById('delete-modal-{{ $product->id }}').style.display='none'" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                                                </div>
                                                <p class="text-sm text-gray-600 mb-4">Delete <strong>{{ $product->name }}</strong>? This cannot be undone.</p>
                                                <form method="POST" action="{{ route('products.destroy', $product) }}" class="space-y-4">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-password-input name="password" required class="w-full pl-3 pr-10 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Your password to confirm" />
                                                    <div class="flex gap-3">
                                                        <button type="submit" class="flex-1 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg text-sm">Delete</button>
                                                        <button type="button" onclick="document.getElementById('delete-modal-{{ $product->id }}').style.display='none'" class="flex-1 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg text-sm">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <script>
                                        document.getElementById('delete-modal-{{ $product->id }}').addEventListener('click', function(e){ if(e.target===this) this.style.display='none'; });
                                        </script>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500">No products found</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
