@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
  <div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center gap-3">
      <a href="{{ route('products.index') }}" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-arrow-left text-lg"></i></a>
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Import Products</h1>
        <p class="text-sm text-gray-500">Bulk import products from CSV or Excel file</p>
      </div>
    </div>

    @if(session('success'))
    <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm font-medium">
      <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm font-medium">
      <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    @if(session('import_errors'))
    <div class="mb-5 p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-800 text-sm">
      <strong><i class="fas fa-exclamation-triangle mr-2"></i>Some rows were skipped:</strong>
      <ul class="mt-2 ml-4 list-disc space-y-1">
        @foreach(session('import_errors') as $err)
        <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
      <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4"><i class="fas fa-file-import mr-2 text-blue-500"></i>Upload File</h2>
      <form action="{{ route('products.import.post') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Select CSV or Excel file</label>
          <input type="file" name="file" accept=".csv,.xlsx,.xls" required
                 class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
          <p class="text-xs text-gray-400 mt-2">Max 10MB. Supported: CSV, XLSX, XLS</p>
        </div>
        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
          <i class="fas fa-upload mr-2"></i>Upload & Import
        </button>
      </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
      <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4"><i class="fas fa-info-circle mr-2 text-blue-500"></i>File Format</h2>
      <p class="text-sm text-gray-600 mb-3">Your file must include these column headers:</p>
      <div class="overflow-x-auto">
        <table class="w-full text-sm border border-gray-200 rounded-lg">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left font-semibold text-gray-600">Column</th>
              <th class="px-4 py-2 text-left font-semibold text-gray-600">Required</th>
              <th class="px-4 py-2 text-left font-semibold text-gray-600">Example</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr><td class="px-4 py-2 font-mono text-blue-600">sku</td><td class="px-4 py-2 text-green-600">Yes</td><td class="px-4 py-2">REB-10MM</td></tr>
            <tr><td class="px-4 py-2 font-mono text-blue-600">name</td><td class="px-4 py-2 text-green-600">Yes</td><td class="px-4 py-2">Rebar 10mm</td></tr>
            <tr><td class="px-4 py-2 font-mono text-blue-600">category</td><td class="px-4 py-2 text-green-600">Yes</td><td class="px-4 py-2">Steel</td></tr>
            <tr><td class="px-4 py-2 font-mono text-blue-600">supplier</td><td class="px-4 py-2 text-green-600">Yes</td><td class="px-4 py-2">SteelCorp Industries</td></tr>
            <tr><td class="px-4 py-2 font-mono text-blue-600">price</td><td class="px-4 py-2 text-green-600">Yes</td><td class="px-4 py-2">250.00</td></tr>
            <tr><td class="px-4 py-2 font-mono text-blue-600">cost</td><td class="px-4 py-2 text-gray-400">No</td><td class="px-4 py-2">180.00</td></tr>
            <tr><td class="px-4 py-2 font-mono text-blue-600">quantity</td><td class="px-4 py-2 text-gray-400">No</td><td class="px-4 py-2">100</td></tr>
            <tr><td class="px-4 py-2 font-mono text-blue-600">low_stock_threshold</td><td class="px-4 py-2 text-gray-400">No</td><td class="px-4 py-2">10</td></tr>
            <tr><td class="px-4 py-2 font-mono text-blue-600">unit</td><td class="px-4 py-2 text-gray-400">No</td><td class="px-4 py-2">pcs</td></tr>
          </tbody>
        </table>
      </div>
      <p class="text-xs text-gray-400 mt-3">Duplicate SKUs will be updated instead of creating new entries.</p>
    </div>
  </div>
</div>
@endsection
