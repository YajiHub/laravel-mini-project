@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-cash-register mr-2"></i>Point of Sale
            </h1>
            <p class="mt-1 text-sm text-gray-600">Process customer transactions</p>
        </div>

        <!-- Alerts -->
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

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md text-red-800">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main POS Area (Left) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Product Search -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-search mr-2"></i>Search Products
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="category-filter" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Name or SKU</label>
                            <input type="text" id="product-search" placeholder="Search by product name or SKU..." class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div id="search-results" class="space-y-2 max-h-96 overflow-y-auto hidden">
                            <!-- Results will be populated here -->
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-boxes mr-2"></i>Products
                    </h2>
                    <div id="products-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Products will be loaded here -->
                        <div class="col-span-full text-center py-8 text-gray-500">
                            <p class="text-sm">Search for products above</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Summary (Right) -->
            <div class="lg:col-span-1">
                <!-- Cart Items -->
                <div class="bg-white shadow rounded-lg p-6 sticky top-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-shopping-cart mr-2"></i>Shopping Cart
                    </h2>

                    <div id="cart-items" class="space-y-3 mb-6 max-h-96 overflow-y-auto">
                        @forelse($cart as $item)
                            <div class="border-b pb-3" data-cart-key="{{ $item['cart_key'] }}">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $item['product_name'] }}</p>
                                        @if($item['variant_name'])
                                            <p class="text-xs text-gray-600">{{ $item['variant_name'] }}</p>
                                        @endif
                                        <p class="text-xs text-gray-500">₱{{ number_format($item['unit_price'], 2) }} each</p>
                                    </div>
                                    <button class="text-red-600 hover:text-red-900 text-sm font-medium remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center border border-gray-300 rounded">
                                        <button class="qty-decrease px-2 py-1 text-gray-600 hover:bg-gray-100">−</button>
                                        <input type="text" class="qty-input w-10 text-center py-1" value="{{ $item['quantity'] }}" readonly>
                                        <button class="qty-increase px-2 py-1 text-gray-600 hover:bg-gray-100">+</button>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900">₱{{ number_format($item['quantity'] * $item['unit_price'], 2) }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <p class="text-sm"><i class="fas fa-inbox mr-2"></i>Cart is empty</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Totals -->
                    <div class="border-t pt-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span id="subtotal" class="font-medium text-gray-900">₱{{ number_format($cartTotal, 2) }}</span>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Discount</label>
                            <div class="flex gap-2">
                                <input type="number" id="discount-amount" placeholder="Amount" step="0.01" min="0" class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <select id="discount-type" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="fixed">Fixed</option>
                                    <option value="percentage">%</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-between text-lg font-semibold border-t pt-3">
                            <span class="text-gray-900">Total:</span>
                            <span id="total" class="text-blue-600">₱{{ number_format($cartTotal, 2) }}</span>
                        </div>
                    </div>

                    <!-- Checkout Form -->
                    <form id="checkout-form" method="POST" action="{{ route('pos.checkout') }}" class="mt-6 space-y-3">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer Name (Optional)</label>
                            <input type="text" name="customer_name" placeholder="Walk-in customer" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                            <select name="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select payment method</option>
                                <option value="cash">Cash</option>
                                <option value="card">Debit/Credit Card</option>
                                <option value="check">Check</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                            <textarea name="notes" rows="2" placeholder="Order notes..." class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <input type="hidden" name="discount" id="discount-value" value="0">
                        <input type="hidden" name="discount_type" id="discount-type-value" value="fixed">

                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-md transition duration-150" id="checkout-btn">
                            <i class="fas fa-check-circle mr-2"></i>Complete Sale
                        </button>

                        <button type="button" class="w-full bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 rounded-md transition duration-150" id="clear-cart-btn">
                            <i class="fas fa-trash mr-2"></i>Clear Cart
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Selection Modal -->
<div id="product-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 id="modal-product-name" class="text-lg font-semibold text-gray-900 mb-4"></h3>

        <div id="modal-variants" class="space-y-3 mb-6">
            <!-- Variants will be loaded here -->
        </div>

        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                <input type="number" id="modal-quantity" value="1" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex gap-3">
                <button type="button" id="modal-add-btn" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-md transition duration-150">
                    <i class="fas fa-plus mr-2"></i>Add to Cart
                </button>
                <button type="button" id="modal-close-btn" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 rounded-md transition duration-150">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// POS System JavaScript
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

// Search products
document.getElementById('product-search').addEventListener('keyup', debounce(function() {
    const search = this.value;
    const categoryId = document.getElementById('category-filter').value;

    if (search.length < 2) {
        document.getElementById('search-results').classList.add('hidden');
        return;
    }

    fetch('{{ route("pos.search") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ q: search, category_id: categoryId })
    })
    .then(res => res.json())
    .then(data => {
        const resultsDiv = document.getElementById('search-results');
        if (data.data.length === 0) {
            resultsDiv.innerHTML = '<p class="text-gray-500 text-sm p-2">No products found</p>';
        } else {
            resultsDiv.innerHTML = data.data.map(product => 
                `<button type="button" class="w-full text-left px-3 py-2 hover:bg-blue-50 rounded product-option" data-product='${JSON.stringify(product)}'>
                    <p class="text-sm font-medium text-gray-900">${product.name}</p>
                    <p class="text-xs text-gray-600">SKU: ${product.sku} - ₱${product.price.toFixed(2)}</p>
                </button>`
            ).join('');
        }
        resultsDiv.classList.remove('hidden');
    });
}, 300));

// Product click handlers
document.addEventListener('click', function(e) {
    if (e.target.closest('.product-option')) {
        const product = JSON.parse(e.target.closest('.product-option').dataset.product);
        showProductModal(product);
    }
});

// Modal functions
function showProductModal(product) {
    document.getElementById('modal-product-name').textContent = product.name + ' (₱' + product.price.toFixed(2) + ')';
    
    const variantsDiv = document.getElementById('modal-variants');
    if (product.has_variants && product.variants.length > 0) {
        variantsDiv.innerHTML = `
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Variant</label>
                <select id="modal-variant" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Standard</option>
                    ${product.variants.map(v => 
                        `<option value="${v.id}" data-price="${v.price_modifier}">${v.name} ${v.price_modifier > 0 ? '+₱' + v.price_modifier.toFixed(2) : ''}</option>`
                    ).join('')}
                </select>
            </div>
        `;
    } else {
        variantsDiv.innerHTML = '';
    }

    document.getElementById('modal-quantity').value = '1';
    document.getElementById('modal-add-btn').onclick = function() {
        addToCart(product);
    };

    document.getElementById('product-modal').classList.remove('hidden');
}

document.getElementById('modal-close-btn').addEventListener('click', function() {
    document.getElementById('product-modal').classList.add('hidden');
});

// Add to cart
function addToCart(product) {
    const quantity = parseInt(document.getElementById('modal-quantity').value);
    const variantSelect = document.getElementById('modal-variant');
    const variantId = variantSelect ? variantSelect.value : null;

    fetch('{{ route("pos.add-to-cart") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: product.id,
            quantity: quantity,
            variant_id: variantId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('product-modal').classList.add('hidden');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

// Clear cart
document.getElementById('clear-cart-btn').addEventListener('click', function() {
    if (confirm('Clear the entire cart?')) {
        location.reload();
        sessionStorage.clear();
    }
});

// Remove item from cart
document.querySelectorAll('.remove-item').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const cartKey = this.closest('[data-cart-key]').dataset.cartKey;
        
        fetch('{{ route("pos.remove-from-cart") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ cart_key: cartKey })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    });
});

// Update quantity
document.querySelectorAll('.qty-increase, .qty-decrease').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const container = this.closest('[data-cart-key]');
        const cartKey = container.dataset.cartKey;
        const input = container.querySelector('.qty-input');
        let qty = parseInt(input.value);

        if (this.classList.contains('qty-increase')) {
            qty++;
        } else {
            qty = Math.max(1, qty - 1);
        }

        fetch('{{ route("pos.update-quantity") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ cart_key: cartKey, quantity: qty })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    });
});

// Discount calculation
function updateTotal() {
    const subtotal = parseFloat(document.getElementById('subtotal').textContent.replace('₱', '').replace(/,/g, ''));
    const discount = parseFloat(document.getElementById('discount-amount').value) || 0;
    const discountType = document.getElementById('discount-type').value;

    let finalDiscount = discount;
    if (discountType === 'percentage') {
        finalDiscount = (subtotal * discount) / 100;
    }

    const total = Math.max(0, subtotal - finalDiscount);
    document.getElementById('total').textContent = '₱' + total.toFixed(2);
    document.getElementById('discount-value').value = discount;
    document.getElementById('discount-type-value').value = discountType;
}

document.getElementById('discount-amount').addEventListener('change', updateTotal);
document.getElementById('discount-type').addEventListener('change', updateTotal);

// Utility: debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Disable checkout if cart is empty
document.getElementById('checkout-form').addEventListener('submit', function(e) {
    const cartItems = document.querySelectorAll('[data-cart-key]');
    if (cartItems.length === 0) {
        e.preventDefault();
        alert('Cart is empty. Add products before checkout.');
    }
});
</script>
@endsection
