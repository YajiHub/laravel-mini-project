@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">
  <div class="max-w-7xl mx-auto space-y-6">

    {{-- Welcome --}}
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
      <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }} &mdash; <span class="font-medium text-blue-600">{{ auth()->user()->role->display_name ?? auth()->user()->role->name }}</span></p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-semibold text-gray-400 uppercase">Products</span>
          <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600"><i class="fas fa-boxes text-sm"></i></span>
        </div>
        <p class="text-2xl font-extrabold text-gray-800">{{ $stats['total_products'] }}</p>
      </div>
      <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-semibold text-gray-400 uppercase">Low Stock</span>
          <span class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center text-yellow-600"><i class="fas fa-exclamation-triangle text-sm"></i></span>
        </div>
        <p class="text-2xl font-extrabold text-yellow-600">{{ $stats['low_stock_count'] }}</p>
      </div>
      <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-semibold text-gray-400 uppercase">Out of Stock</span>
          <span class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-600"><i class="fas fa-times-circle text-sm"></i></span>
        </div>
        <p class="text-2xl font-extrabold text-red-600">{{ $stats['out_of_stock'] }}</p>
      </div>
      <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-semibold text-gray-400 uppercase">Today's Sales</span>
          <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center text-green-600"><i class="fas fa-cash-register text-sm"></i></span>
        </div>
        <p class="text-2xl font-extrabold text-green-600">₱{{ number_format($stats['total_sales_today'], 2) }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      {{-- Stock Activity Chart --}}
      <div class="lg:col-span-2 bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <h2 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-chart-bar mr-2 text-blue-500"></i>Stock Activity — Last 7 Days</h2>
        <canvas id="stockChart" height="90"></canvas>
      </div>

      {{-- Quick Links --}}
      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <h2 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-bolt mr-2 text-yellow-500"></i>Quick Actions</h2>
        <div class="space-y-2">
          @php $role = auth()->user()->role->name; @endphp

          @if($role === 'cashier')
            <a href="{{ route('pos.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-green-50 hover:bg-green-100 transition text-green-700 font-medium text-sm">
              <i class="fas fa-cash-register w-5 text-center"></i>Open POS Terminal
            </a>
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition text-gray-700 font-medium text-sm">
              <i class="fas fa-user w-5 text-center"></i>My Profile
            </a>

          @elseif($role === 'inventory_manager')
            <a href="{{ route('products.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 hover:bg-blue-100 transition text-blue-700 font-medium text-sm">
              <i class="fas fa-boxes w-5 text-center"></i>Products
            </a>
            <a href="{{ route('categories.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition text-indigo-700 font-medium text-sm">
              <i class="fas fa-tags w-5 text-center"></i>Categories
            </a>
            <a href="{{ route('suppliers.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-purple-50 hover:bg-purple-100 transition text-purple-700 font-medium text-sm">
              <i class="fas fa-truck w-5 text-center"></i>Suppliers
            </a>
            <a href="{{ route('stock-transactions.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-yellow-50 hover:bg-yellow-100 transition text-yellow-700 font-medium text-sm">
              <i class="fas fa-dolly w-5 text-center"></i>Stock Transactions
            </a>

          @elseif($role === 'store_manager')
            <a href="{{ route('pos.transactions') }}" class="flex items-center gap-3 p-3 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition text-indigo-700 font-medium text-sm">
              <i class="fas fa-receipt w-5 text-center"></i>Transaction History
            </a>
            <a href="{{ route('settings.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition text-gray-700 font-medium text-sm">
              <i class="fas fa-cog w-5 text-center"></i>Store Settings
            </a>

          @elseif($role === 'admin')
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-red-50 hover:bg-red-100 transition text-red-700 font-medium text-sm">
              <i class="fas fa-users-cog w-5 text-center"></i>User Management
            </a>
            <a href="{{ route('admin.users.create') }}" class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 hover:bg-blue-100 transition text-blue-700 font-medium text-sm">
              <i class="fas fa-user-plus w-5 text-center"></i>Create New User
            </a>
            <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-yellow-50 hover:bg-yellow-100 transition text-yellow-700 font-medium text-sm">
              <i class="fas fa-clipboard-list w-5 text-center"></i>Audit Logs
            </a>
          @endif
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      {{-- Low Stock --}}
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-800"><i class="fas fa-exclamation-triangle mr-2 text-yellow-500"></i>Low Stock Items</h2>
          @if(in_array(auth()->user()->role->name, ['inventory_manager','admin']))
          <a href="{{ route('products.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
          @endif
        </div>
        @forelse($lowStockProducts as $product)
        <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between hover:bg-gray-50">
          <div>
            <p class="text-sm font-medium text-gray-800">{{ $product->name }}</p>
            <p class="text-xs text-gray-500">{{ $product->category?->name }} &bull; SKU: {{ $product->sku }}</p>
          </div>
          <span class="text-sm font-bold {{ $product->quantity == 0 ? 'text-red-600' : 'text-yellow-600' }}">
            {{ $product->quantity }} left
          </span>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-gray-400 text-sm">
          <i class="fas fa-check-circle text-green-400 text-2xl mb-2 block"></i>All products are well stocked
        </div>
        @endforelse
      </div>

      {{-- Top Products --}}
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
          <h2 class="text-base font-semibold text-gray-800"><i class="fas fa-trophy mr-2 text-yellow-400"></i>Top Selling Products</h2>
        </div>
        @forelse($topProducts as $i => $product)
        <div class="px-5 py-3 border-b border-gray-50 flex items-center gap-4 hover:bg-gray-50">
          <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 text-xs font-bold flex items-center justify-center">{{ $i+1 }}</span>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-800">{{ $product->product_name }}</p>
            <p class="text-xs text-gray-500">{{ $product->total_sold }} units sold</p>
          </div>
          <span class="text-sm font-bold text-green-600">₱{{ number_format($product->revenue, 2) }}</span>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-gray-400 text-sm">
          <i class="fas fa-chart-bar text-2xl mb-2 block"></i>No sales data yet
        </div>
        @endforelse
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('stockChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: @json($chartData->pluck('date')),
    datasets: [
      { label: 'Stock In', data: @json($chartData->pluck('stock_in')), backgroundColor: '#22c55e', borderRadius: 4 },
      { label: 'Stock Out', data: @json($chartData->pluck('stock_out')), backgroundColor: '#ef4444', borderRadius: 4 }
    ]
  },
  options: {
    responsive: true,
    plugins: { legend: { position: 'top' } },
    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
  }
});
</script>
@endsection