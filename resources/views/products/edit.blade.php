@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
  <div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center gap-3">
      <a href="{{ route('products.show', $product) }}" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-arrow-left text-lg"></i></a>
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Product</h1>
        <p class="text-sm text-gray-500">{{ $product->name }} &mdash; {{ $product->sku }}</p>
      </div>
    </div>

    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm">
      <strong>Please fix the following errors:</strong>
      <ul class="mt-2 ml-4 list-disc space-y-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
      @csrf
      @method('PUT')

      {{-- Product Image --}}
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4"><i class="fas fa-image mr-2 text-blue-500"></i>Product Image</h2>

        @if($product->image)
        {{-- Existing image --}}
        <div id="existing-image-block" class="mb-4">
          <p class="text-xs text-gray-500 mb-2">Current image:</p>
          <div class="relative inline-block">
            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-36 rounded-xl object-cover shadow-sm border border-gray-200">
            <button type="button" onclick="removeExistingImage()" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs flex items-center justify-center hover:bg-red-600 transition shadow">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <input type="hidden" name="remove_image" id="remove-image-flag" value="0">
          <p id="remove-notice" class="hidden text-xs text-red-600 mt-2"><i class="fas fa-exclamation-circle mr-1"></i>Image will be removed on save. Upload a new one below to replace it.</p>
        </div>
        @endif

        <div id="drop-zone" class="relative border-2 border-dashed border-gray-300 rounded-xl p-6 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition group {{ $product->image ? 'mt-2' : '' }}" onclick="document.getElementById('image-input').click()">
          <div id="drop-placeholder">
            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 group-hover:text-blue-500 transition mb-2"></i>
            <p class="text-sm font-medium text-gray-600">{{ $product->image ? 'Upload a replacement image' : 'Click or drag & drop an image here' }}</p>
            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP — max 2MB</p>
          </div>
          <img id="image-preview" src="" alt="Preview" class="hidden mx-auto max-h-40 rounded-lg shadow-sm object-contain mt-2">
          <input type="file" id="image-input" name="image" accept="image/jpg,image/jpeg,image/png,image/webp" class="hidden">
          <button type="button" id="remove-new-btn" onclick="removeNewImage(event)" class="hidden absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs flex items-center justify-center hover:bg-red-600 transition">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      {{-- Core Details --}}
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4"><i class="fas fa-info-circle mr-2 text-blue-500"></i>Product Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SKU <span class="text-red-500">*</span></label>
            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Unit of Measure</label>
            <input type="text" name="unit" value="{{ old('unit', $product->unit) }}" placeholder="e.g., pcs, bag, roll, kg" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
            <select name="category_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option value="">Select Category</option>
              @foreach($categories as $c)
              <option value="{{ $c->id }}" {{ old('category_id', $product->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500">*</span></label>
            <select name="supplier_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option value="">Select Supplier</option>
              @foreach($suppliers as $s)
              <option value="{{ $s->id }}" {{ old('supplier_id', $product->supplier_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description', $product->description) }}</textarea>
          </div>
        </div>
      </div>

      {{-- Pricing & Stock --}}
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4"><i class="fas fa-peso-sign mr-2 text-green-500"></i>Pricing & Stock</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price (₱) <span class="text-red-500">*</span></label>
            <input type="number" name="price" value="{{ old('price', $product->price) }}" required min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Unit Cost (₱) <span class="text-red-500">*</span></label>
            <input type="number" name="cost" value="{{ old('cost', $product->cost) }}" required min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
            <input type="number" name="quantity" value="{{ old('quantity', $product->quantity) }}" required min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reorder / Low Stock Level <span class="text-red-500">*</span></label>
            <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" required min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>
        </div>
      </div>

      {{-- Status --}}
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center gap-3">
        <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
        <label for="is_active" class="text-sm font-medium text-gray-700">Active — visible in POS and inventory</label>
      </div>

      {{-- Actions --}}
      <div class="flex justify-end gap-3">
        <a href="{{ route('products.show', $product) }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">Cancel</a>
        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm transition"><i class="fas fa-save mr-2"></i>Update Product</button>
      </div>
    </form>
  </div>
</div>

<script>
const imageInput = document.getElementById('image-input');
const preview = document.getElementById('image-preview');
const placeholder = document.getElementById('drop-placeholder');
const removeNewBtn = document.getElementById('remove-new-btn');
const dropZone = document.getElementById('drop-zone');

imageInput.addEventListener('change', e => showPreview(e.target.files[0]));

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-blue-500','bg-blue-50'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-blue-500','bg-blue-50'));
dropZone.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.classList.remove('border-blue-500','bg-blue-50');
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    const dt = new DataTransfer();
    dt.items.add(file);
    imageInput.files = dt.files;
    showPreview(file);
  }
});

function showPreview(file) {
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    preview.src = e.target.result;
    preview.classList.remove('hidden');
    placeholder.classList.add('hidden');
    removeNewBtn.classList.remove('hidden');
  };
  reader.readAsDataURL(file);
}

function removeNewImage(e) {
  e.stopPropagation();
  imageInput.value = '';
  preview.src = '';
  preview.classList.add('hidden');
  placeholder.classList.remove('hidden');
  removeNewBtn.classList.add('hidden');
}

function removeExistingImage() {
  document.getElementById('existing-image-block').style.opacity = '0.4';
  document.getElementById('remove-image-flag').value = '1';
  document.getElementById('remove-notice').classList.remove('hidden');
}
</script>
@endsection
