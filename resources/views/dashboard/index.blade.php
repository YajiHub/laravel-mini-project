@extends('layouts.app')
@section('content')
@php $role = auth()->user()->role->name; @endphp
<div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">
<div class="max-w-7xl mx-auto space-y-6">

<div>
  <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
  <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }} &mdash; <span class="font-medium text-blue-600">{{ auth()->user()->role->display_name ?? auth()->user()->role->name }}</span></p>
</div>

{{-- ============================================================ --}}
{{-- CASHIER DASHBOARD --}}
{{-- ============================================================ --}}
@if($role === 'cashier')
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
  <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-semibold text-gray-400 uppercase">Today's Sales</span>
      <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center text-green-600"><i class="fas fa-cash-register text-sm"></i></span>
    </div>
    <p class="text-2xl font-extrabold text-green-600">&#x20b1;{{ number_format($stats['total_sales_today'], 2) }}</p>
  </div>
  <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-semibold text-gray-400 uppercase">Transactions Today</span>
      <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600"><i class="fas fa-receipt text-sm"></i></span>
    </div>
    <p class="text-2xl font-extrabold text-gray-800">{{ $stats['transactions_today'] ?? 0 }}</p>
  </div>
  <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-semibold text-gray-400 uppercase">Products Available</span>
      <span class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600"><i class="fas fa-boxes text-sm"></i></span>
    </div>
    <p class="text-2xl font-extrabold text-gray-800">{{ $stats['total_products'] }}</p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-1 bg-white rounded-xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-bolt mr-2 text-yellow-500"></i>Quick Actions</h2>
    <div class="space-y-2">
      <a href="{{ route('pos.index') }}" class="flex items-center gap-3 p-4 rounded-xl bg-green-600 hover:bg-green-700 text-white font-semibold text-sm transition">
        <i class="fas fa-cash-register text-lg"></i>Open POS Terminal
      </a>
      <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-700 font-medium text-sm transition">
        <i class="fas fa-user w-5 text-center"></i>My Profile
      </a>
    </div>
  </div>
  <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
      <h2 class="text-base font-semibold text-gray-800"><i class="fas fa-receipt mr-2 text-blue-500"></i>My Recent Sales Today</h2>
    </div>
    @forelse($recentSales ?? [] as $sale)
    <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between hover:bg-gray-50">
      <div>
        <p class="text-sm font-medium text-gray-800">{{ $sale->transaction_number }}</p>
        <p class="text-xs text-gray-500">{{ $sale->transaction_date?->format('g:i A') }} &bull; {{ $sale->customer_name ?? 'Walk-in' }}</p>
      </div>
      <div class="flex items-center gap-3">
        <span class="text-sm font-bold text-green-600">&#x20b1;{{ number_format($sale->total, 2) }}</span>
        <a href="{{ route('pos.receipt', $sale) }}" class="text-xs text-blue-500 hover:text-blue-700"><i class="fas fa-eye"></i></a>
      </div>
    </div>
    @empty
    <div class="px-5 py-10 text-center text-gray-400 text-sm">
      <i class="fas fa-receipt text-3xl mb-2 block text-gray-300"></i>No sales processed today yet
    </div>
    @endforelse
  </div>
</div>

{{-- ============================================================ --}}
{{-- INVENTORY MANAGER DASHBOARD --}}
{{-- ============================================================ --}}
@elseif($role === 'inventory_manager')
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
      <span class="text-xs font-semibold text-gray-400 uppercase">Categories</span>
      <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600"><i class="fas fa-tags text-sm"></i></span>
    </div>
    <p class="text-2xl font-extrabold text-gray-800">{{ $stats['total_categories'] }}</p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 bg-white rounded-xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-chart-bar mr-2 text-blue-500"></i>Stock Activity — Last 7 Days</h2>
    <canvas id="stockChart" height="90"></canvas>
  </div>
  <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-bolt mr-2 text-yellow-500"></i>Quick Actions</h2>
    <div class="space-y-2">
      <a href="{{ route('products.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium text-sm transition"><i class="fas fa-boxes w-5 text-center"></i>Products</a>
      <a href="{{ route('categories.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium text-sm transition"><i class="fas fa-tags w-5 text-center"></i>Categories</a>
      <a href="{{ route('suppliers.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 font-medium text-sm transition"><i class="fas fa-truck w-5 text-center"></i>Suppliers</a>
      <a href="{{ route('stock-transactions.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-yellow-50 hover:bg-yellow-100 text-yellow-700 font-medium text-sm transition"><i class="fas fa-dolly w-5 text-center"></i>Stock Movements</a>
    </div>
  </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
    <h2 class="text-base font-semibold text-gray-800"><i class="fas fa-exclamation-triangle mr-2 text-yellow-500"></i>Low Stock Items</h2>
    <a href="{{ route('products.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
  </div>
  @forelse($lowStockProducts as $product)
  <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between hover:bg-gray-50">
    <div>
      <p class="text-sm font-medium text-gray-800">{{ $product->name }}</p>
      <p class="text-xs text-gray-500">{{ $product->category?->name }} &bull; SKU: {{ $product->sku }}</p>
    </div>
    <span class="text-sm font-bold {{ $product->quantity == 0 ? 'text-red-600' : 'text-yellow-600' }}">{{ $product->quantity }} left</span>
  </div>
  @empty
  <div class="px-5 py-8 text-center text-gray-400 text-sm"><i class="fas fa-check-circle text-green-400 text-2xl mb-2 block"></i>All products are well stocked</div>
  @endforelse
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('stockChart').getContext('2d'), {
  type: 'bar',
  data: { labels: @json($chartData->pluck('date')), datasets: [
    { label: 'Stock In', data: @json($chartData->pluck('stock_in')), backgroundColor: '#22c55e', borderRadius: 4 },
    { label: 'Stock Out', data: @json($chartData->pluck('stock_out')), backgroundColor: '#ef4444', borderRadius: 4 }
  ]},
  options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
});
</script>

{{-- ============================================================ --}}
{{-- STORE MANAGER DASHBOARD --}}
{{-- ============================================================ --}}
@elseif($role === 'store_manager')
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
  <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-semibold text-gray-400 uppercase">Today's Revenue</span>
      <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center text-green-600"><i class="fas fa-peso-sign text-sm"></i></span>
    </div>
    <p class="text-2xl font-extrabold text-green-600">&#x20b1;{{ number_format($stats['total_sales_today'], 2) }}</p>
  </div>
  <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-semibold text-gray-400 uppercase">Transactions Today</span>
      <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600"><i class="fas fa-receipt text-sm"></i></span>
    </div>
    <p class="text-2xl font-extrabold text-gray-800">{{ $stats['transactions_today'] ?? 0 }}</p>
  </div>
  <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-semibold text-gray-400 uppercase">This Month</span>
      <span class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600"><i class="fas fa-calendar text-sm"></i></span>
    </div>
    <p class="text-2xl font-extrabold text-gray-800">&#x20b1;{{ number_format($stats['sales_this_month'] ?? 0, 2) }}</p>
  </div>
  <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-semibold text-gray-400 uppercase">Low Stock Items</span>
      <span class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center text-yellow-600"><i class="fas fa-exclamation-triangle text-sm"></i></span>
    </div>
    <p class="text-2xl font-extrabold text-yellow-600">{{ $stats['low_stock_count'] }}</p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 bg-white rounded-xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-chart-line mr-2 text-green-500"></i>Sales Revenue — Last 7 Days</h2>
    <canvas id="salesChart" height="90"></canvas>
  </div>
  <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-bolt mr-2 text-yellow-500"></i>Quick Actions</h2>
    <div class="space-y-2">
      <a href="{{ route('pos.transactions') }}" class="flex items-center gap-3 p-3 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium text-sm transition"><i class="fas fa-receipt w-5 text-center"></i>Transaction History</a>
      <a href="{{ route('reports.sales-pdf') }}" class="flex items-center gap-3 p-3 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 font-medium text-sm transition"><i class="fas fa-file-pdf w-5 text-center"></i>Sales PDF Report</a>
      <a href="{{ route('reports.sales-csv') }}" class="flex items-center gap-3 p-3 rounded-lg bg-green-50 hover:bg-green-100 text-green-700 font-medium text-sm transition"><i class="fas fa-file-csv w-5 text-center"></i>Export Sales CSV</a>
      <a href="{{ route('settings.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-700 font-medium text-sm transition"><i class="fas fa-cog w-5 text-center"></i>Store Settings</a>
    </div>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
      <span class="text-sm font-bold text-green-600">&#x20b1;{{ number_format($product->revenue, 2) }}</span>
    </div>
    @empty
    <div class="px-5 py-8 text-center text-gray-400 text-sm"><i class="fas fa-chart-bar text-2xl mb-2 block"></i>No sales data yet</div>
    @endforelse
  </div>

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
      <h2 class="text-base font-semibold text-gray-800"><i class="fas fa-clock mr-2 text-blue-500"></i>Recent Transactions</h2>
      <a href="{{ route('pos.transactions') }}" class="text-xs text-blue-600 hover:underline">View all</a>
    </div>
    @forelse($recentSales ?? [] as $sale)
    <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between hover:bg-gray-50">
      <div>
        <p class="text-sm font-medium text-gray-800">{{ $sale->transaction_number }}</p>
        <p class="text-xs text-gray-500">{{ $sale->transaction_date?->format('M d g:i A') }} &bull; {{ $sale->user?->name }}</p>
      </div>
      <div class="flex items-center gap-3">
        <span class="text-sm font-bold text-green-600">&#x20b1;{{ number_format($sale->total, 2) }}</span>
        <a href="{{ route('pos.receipt', $sale) }}" class="text-xs text-blue-500 hover:text-blue-700"><i class="fas fa-eye"></i></a>
      </div>
    </div>
    @empty
    <div class="px-5 py-8 text-center text-gray-400 text-sm">No recent transactions</div>
    @endforelse
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('salesChart').getContext('2d'), {
  type: 'line',
  data: { labels: @json($salesChartData->pluck('date') ?? collect()->pluck('date')), datasets: [
    { label: 'Revenue (₱)', data: @json($salesChartData->pluck('revenue') ?? collect()->pluck('revenue')), borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,.1)', tension: 0.4, fill: true }
  ]},
  options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
});
</script>

{{-- ============================================================ --}}
{{-- ADMIN DASHBOARD --}}
{{-- ============================================================ --}}
@elseif($role === 'admin')
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
  <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-semibold text-gray-400 uppercase">Active Users</span>
      <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600"><i class="fas fa-users text-sm"></i></span>
    </div>
    <p class="text-2xl font-extrabold text-gray-800">{{ $stats['total_users'] }}</p>
    <p class="text-xs text-gray-400 mt-1">Registered system accounts</p>
  </div>
  <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-semibold text-gray-400 uppercase">User Roles</span>
      <span class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600"><i class="fas fa-user-shield text-sm"></i></span>
    </div>
    <p class="text-2xl font-extrabold text-gray-800">{{ $stats['total_roles'] }}</p>
    <p class="text-xs text-gray-400 mt-1">Active role definitions</p>
  </div>
  <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-semibold text-gray-400 uppercase">Audit Events Today</span>
      <span class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center text-yellow-600"><i class="fas fa-clipboard-list text-sm"></i></span>
    </div>
    <p class="text-2xl font-extrabold text-gray-800">{{ $stats['audit_logs_today'] }}</p>
    <p class="text-xs text-gray-400 mt-1">System activity logs</p>
  </div>
  <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-semibold text-gray-400 uppercase">System Status</span>
      <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center text-green-600"><i class="fas fa-check-circle text-sm"></i></span>
    </div>
    <p class="text-2xl font-extrabold text-green-600">Online</p>
    <p class="text-xs text-gray-400 mt-1">All services running</p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-1 bg-white rounded-xl p-6 shadow-sm border border-gray-100">
    <h2 class="text-base font-semibold text-gray-800 mb-4"><i class="fas fa-bolt mr-2 text-yellow-500"></i>Quick Actions</h2>
    <div class="space-y-2">
      <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium text-sm transition"><i class="fas fa-users-cog w-5 text-center"></i>User Management</a>
      <a href="{{ route('admin.users.create') }}" class="flex items-center gap-3 p-3 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium text-sm transition"><i class="fas fa-user-plus w-5 text-center"></i>Create New User</a>
      <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-yellow-50 hover:bg-yellow-100 text-yellow-700 font-medium text-sm transition"><i class="fas fa-clipboard-list w-5 text-center"></i>Audit Logs</a>
      <a href="{{ route('categories.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 font-medium text-sm transition"><i class="fas fa-tags w-5 text-center"></i>Manage Categories</a>
    </div>
  </div>
  <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
      <h2 class="text-base font-semibold text-gray-800"><i class="fas fa-clipboard-list mr-2 text-yellow-500"></i>Recent System Activity</h2>
      <a href="{{ route('admin.audit-logs.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
    </div>
    @forelse($recentActivities as $log)
    <div class="px-5 py-3 border-b border-gray-50 flex items-center gap-4 hover:bg-gray-50">
      @php $color = ['create'=>'green','created'=>'green','update'=>'blue','updated'=>'blue','delete'=>'red','deleted'=>'red','login'=>'indigo','logout'=>'gray'][$log->action] ?? 'yellow'; @endphp
      <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-800 shrink-0">{{ ucfirst($log->action) }}</span>
      <div class="flex-1 min-w-0">
        <p class="text-sm text-gray-700 truncate">{{ $log->description ?? ($log->action . ' on ' . class_basename($log->model_type ?? 'System')) }}</p>
        <p class="text-xs text-gray-400">{{ $log->user?->name ?? 'System' }} &bull; {{ $log->created_at->diffForHumans() }}</p>
      </div>
    </div>
    @empty
    <div class="px-5 py-10 text-center">
      <i class="fas fa-clipboard-list text-3xl text-gray-300 mb-3"></i>
      <p class="text-sm font-medium text-gray-400">No activity logged yet</p>
      <p class="text-xs text-gray-400 mt-1">Activity is recorded as users interact with the system</p>
    </div>
    @endforelse
  </div>
</div>
@endif

</div>
</div>
@endsection