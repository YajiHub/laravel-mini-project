@extends('layouts.app')

@section('content')
<style>
.pos-wrap{display:flex;height:calc(100vh - 64px);overflow:hidden;background:#f1f5f9}
.pos-left{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:12px}
.pos-right{width:380px;background:#fff;border-left:1px solid #e2e8f0;display:flex;flex-direction:column;box-shadow:-2px 0 8px rgba(0,0,0,.06)}
.cat-tabs{display:flex;gap:6px;flex-wrap:wrap}
.cat-tab{padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;border:2px solid #e2e8f0;background:#fff;color:#64748b;transition:all .15s}
.cat-tab.active,.cat-tab:hover{background:#2563eb;color:#fff;border-color:#2563eb}
.prod-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px}
.prod-card{background:#fff;border-radius:10px;padding:12px;cursor:pointer;border:2px solid transparent;transition:all .15s;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.prod-card:hover{border-color:#2563eb;box-shadow:0 4px 12px rgba(37,99,235,.15)}
.prod-card.out-of-stock{opacity:.5;cursor:not-allowed}
.prod-icon{width:100%;height:72px;background:linear-gradient(135deg,#dbeafe,#ede9fe);border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:8px;font-size:28px}
.prod-name{font-size:13px;font-weight:600;color:#1e293b;line-height:1.3;margin-bottom:4px}
.prod-price{font-size:15px;font-weight:700;color:#2563eb}
.prod-stock{font-size:11px;color:#64748b}
.cart-header{padding:16px;border-bottom:1px solid #f1f5f9;background:#f8fafc}
.cart-title{font-size:16px;font-weight:700;color:#1e293b}
.cart-num{font-size:12px;color:#64748b;margin-top:2px}
.cart-items{flex:1;overflow-y:auto;padding:12px}
.cart-item{background:#f8fafc;border-radius:8px;padding:10px 12px;margin-bottom:8px;display:flex;gap:10px;align-items:flex-start}
.cart-item-info{flex:1;min-width:0}
.cart-item-name{font-size:13px;font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cart-item-sub{font-size:11px;color:#64748b}
.cart-item-price{font-size:13px;font-weight:700;color:#1e293b;white-space:nowrap}
.qty-ctrl{display:flex;align-items:center;gap:4px;margin-top:4px}
.qty-btn{width:24px;height:24px;border-radius:6px;background:#e2e8f0;border:none;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;font-weight:700;color:#374151}
.qty-btn:hover{background:#cbd5e1}
.qty-val{width:32px;text-align:center;font-size:13px;font-weight:600}
.remove-btn{background:none;border:none;color:#ef4444;cursor:pointer;padding:2px;font-size:13px}
.cart-footer{padding:14px 16px;border-top:1px solid #e2e8f0;background:#fff}
.total-row{display:flex;justify-content:space-between;font-size:13px;color:#64748b;margin-bottom:4px}
.total-row.grand{font-size:16px;font-weight:700;color:#1e293b;border-top:1px solid #e2e8f0;padding-top:8px;margin-top:4px}
.pay-methods{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin:10px 0}
.pay-btn{padding:8px;border:2px solid #e2e8f0;border-radius:8px;background:#fff;font-size:12px;font-weight:600;color:#374151;cursor:pointer;text-align:center;transition:all .15s}
.pay-btn.active{border-color:#2563eb;background:#eff6ff;color:#2563eb}
.charge-btn{width:100%;padding:14px;background:#16a34a;color:#fff;border:none;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;transition:background .15s;margin-top:8px}
.charge-btn:hover{background:#15803d}
.charge-btn:disabled{background:#9ca3af;cursor:not-allowed}
.void-btn{width:100%;padding:8px;background:#fff;color:#ef4444;border:2px solid #fecaca;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;margin-top:6px}
.void-btn:hover{background:#fef2f2}
.field-sm{width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;transition:border .15s}
.field-sm:focus{border-color:#2563eb}
.field-lbl{font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px;text-transform:uppercase;letter-spacing:.4px}
.sku-bar{display:flex;gap:6px}
.sku-input{flex:1;padding:8px 12px;border:2px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none}
.sku-input:focus{border-color:#2563eb}
.empty-cart{text-align:center;padding:40px 20px;color:#94a3b8}
.empty-cart i{font-size:40px;margin-bottom:10px;display:block}
.toast{position:fixed;top:20px;right:20px;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;z-index:9999;transform:translateX(120%);transition:transform .25s;max-width:300px}
.toast.show{transform:translateX(0)}
.toast.success{background:#16a34a;color:#fff}
.toast.error{background:#ef4444;color:#fff}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;overflow-y:auto;padding:20px 16px}
.modal-overlay.show{display:block}
.modal-box{background:#fff;border-radius:12px;padding:24px;width:360px;max-width:100%;margin:0 auto}
</style>

<div class="pos-wrap">
  {{-- LEFT: Product Panel --}}
  <div class="pos-left">
    {{-- SKU Quick-Add --}}
    <div class="bg-white rounded-xl p-12 shadow-sm border border-gray-100">
      <div class="field-lbl mb-1"><i class="fas fa-barcode mr-1"></i>Quick Add by SKU / Barcode</div>
      <div class="sku-bar">
        <input type="text" id="sku-input" class="sku-input" placeholder="Type or scan SKU then press Enter..." autocomplete="off">
        <button onclick="lookupSku()" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">Add</button>
      </div>
    </div>

    {{-- Category Tabs --}}
    <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
      <div class="cat-tabs">
        <button class="cat-tab active" onclick="filterCat(null,this)">All</button>
        @foreach($categories as $cat)
          <button class="cat-tab" onclick="filterCat({{ $cat->id }},this)">{{ $cat->name }}</button>
        @endforeach
      </div>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-xl px-3 py-2 shadow-sm border border-gray-100 flex items-center gap-2">
      <i class="fas fa-search text-gray-400"></i>
      <input type="text" id="search-input" placeholder="Search products..." class="flex-1 text-sm outline-none py-1">
    </div>

    {{-- Products Grid --}}
    <div id="prod-grid" class="prod-grid"></div>
  </div>

  {{-- RIGHT: Order Panel --}}
  <div class="pos-right">
    <div class="cart-header">
      <div class="flex items-center justify-between">
        <div>
          <div class="cart-title"><i class="fas fa-receipt mr-1 text-blue-600"></i>Current Order</div>
          <div class="cart-num" id="order-num">New Order</div>
        </div>
        <button onclick="clearCart()" class="text-xs text-gray-400 hover:text-red-500 transition"><i class="fas fa-trash mr-1"></i>Clear</button>
      </div>
    </div>

    <div class="cart-items" id="cart-items">
      <div class="empty-cart" id="empty-msg">
        <i class="fas fa-shopping-basket"></i>
        <p class="font-semibold text-sm">Cart is empty</p>
        <p class="text-xs mt-1">Click a product or scan a SKU to add items</p>
      </div>
      <div id="cart-list"></div>
    </div>

    <div class="cart-footer">
      {{-- Totals --}}
      <div class="total-row"><span>Subtotal</span><span id="disp-subtotal">{{ $settings['currency_symbol'] ?? '₱' }}0.00</span></div>
      @if(($settings['tax_rate'] ?? 0) > 0)
      <div class="total-row"><span id="tax-label-row">{{ $settings['tax_label'] ?? 'VAT' }} ({{ $settings['tax_rate'] }}%)</span><span id="disp-tax">{{ $settings['currency_symbol'] ?? '₱' }}0.00</span></div>
      @endif
      <div class="total-row">
        <span>Discount</span>
        <span>
          <input type="number" id="discount-val" value="0" min="0" step="0.01" style="width:70px;text-align:right;border:1px solid #e2e8f0;border-radius:6px;padding:2px 6px;font-size:12px" onchange="recalc()">
          <select id="discount-type" style="border:1px solid #e2e8f0;border-radius:6px;padding:2px 4px;font-size:12px" onchange="recalc()">
            <option value="fixed">₱</option>
            <option value="percentage">%</option>
          </select>
        </span>
      </div>
      <div class="total-row grand"><span>TOTAL</span><span id="disp-total">{{ $settings['currency_symbol'] ?? '₱' }}0.00</span></div>

      {{-- Customer --}}
      <div style="margin:8px 0 6px">
        <div class="field-lbl">Customer Name (optional)</div>
        <input type="text" id="customer-name" class="field-sm" placeholder="Walk-in Customer">
      </div>

      {{-- Cash tendered --}}
      <div>
        <div class="field-lbl">Cash Tendered</div>
        <input type="number" id="cash-tendered" class="field-sm" placeholder="Enter amount" min="0" step="0.01" oninput="calcChange()">
        <div class="total-row" style="margin-top:6px"><span style="color:#16a34a;font-weight:600">Change</span><span id="disp-change" style="color:#16a34a;font-weight:700">{{ $settings['currency_symbol'] ?? '₱' }}0.00</span></div>
      </div>

      <button class="charge-btn" id="charge-btn" onclick="processCheckout()">
        <i class="fas fa-check-circle mr-2"></i>Charge <span id="charge-amt">{{ $settings['currency_symbol'] ?? '₱' }}0.00</span>
      </button>
    </div>
  </div>
</div>

{{-- Variant Modal --}}
<div class="modal-overlay" id="variant-modal">
  <div class="modal-box">
    <h3 id="modal-title" class="text-base font-bold text-gray-800 mb-4"></h3>
    <div id="modal-variants" class="mb-4"></div>
    <div class="mb-4">
      <div class="field-lbl">Quantity</div>
      <input type="number" id="modal-qty" class="field-sm" value="1" min="1">
    </div>
    <div style="display:flex;gap:12px;margin-top:8px">
      <button onclick="addFromModal()" style="flex:1;padding:10px 0;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:background .15s" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
        <i class="fas fa-plus-circle" style="margin-right:6px"></i>Add to Order
      </button>
      <button onclick="closeModal()" style="flex:1;padding:10px 0;background:#e5e7eb;color:#374151;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:background .15s" onmouseover="this.style.background='#d1d5db'" onmouseout="this.style.background='#e5e7eb'">
        Cancel
      </button>
    </div>
  </div>
</div>

<div id="toast" class="toast"></div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const CURRENCY = @json($settings['currency_symbol'] ?? '₱');
const SEARCH_URL  = '{{ route("pos.search") }}';
const CART_URL    = '{{ route("pos.cart") }}';
const ADD_URL     = '{{ route("pos.add-to-cart") }}';
const REMOVE_URL  = '{{ route("pos.remove-from-cart") }}';
const UPDATE_URL  = '{{ route("pos.update-quantity") }}';
const CLEAR_URL   = '{{ route("pos.clear-cart") }}';
const SKU_URL     = '{{ route("pos.lookup-sku") }}';
const CHECKOUT_URL= '{{ route("pos.checkout") }}';

let cartData = {cart:[],subtotal:0,tax_rate:0,tax_amount:0,total:0,currency:CURRENCY};
let currentPay = 'cash';
let currentProduct = null;
let currentCatId = null;
let searchTimer = null;

// ---- Init ----
async function init() {
  await loadCart();
  await loadProducts('', null);
}

// ---- Products ----
async function loadProducts(q, catId) {
  const r = await apiFetch(SEARCH_URL, {q: q || '', category_id: catId || ''});
  if (r.success) renderProducts(r.data);
  else console.error('Search failed', r);
}

window._posProducts = {};

function renderProducts(prods) {
  const g = document.getElementById('prod-grid');
  window._posProducts = {};
  if (!prods.length) {
    g.innerHTML = '<p class="col-span-full text-center py-8 text-gray-400 text-sm">No products found</p>';
    return;
  }
  prods.forEach(p => { window._posProducts[p.id] = p; });
  g.innerHTML = prods.map(p => {
    const oos = p.quantity <= 0;
    const stockBadge = oos
      ? '<span style="color:#ef4444">Out of stock</span>'
      : 'Stock: ' + p.quantity + (p.unit ? ' ' + p.unit : '');
    const imgHtml = p.image
      ? '<img src="' + p.image + '" alt="' + p.name + '" style="width:100%;height:80px;object-fit:cover;border-radius:8px;margin-bottom:6px;display:block">'
      : '<div class="prod-icon"><i class="fas fa-box-open text-blue-400"></i></div>';
    return '<div class="prod-card' + (oos ? ' out-of-stock' : '') + '" data-pid="' + p.id + '">' +
      imgHtml +
      '<div class="prod-name">' + p.name + '</div>' +
      '<div class="prod-price">' + CURRENCY + p.price.toFixed(2) + '</div>' +
      '<div class="prod-stock">' + stockBadge + '</div>' +
      '</div>';
  }).join('');
}

// Event delegation for product card clicks
document.getElementById('prod-grid').addEventListener('click', function(e) {
  const card = e.target.closest('.prod-card');
  if (!card || card.classList.contains('out-of-stock')) return;
  const pid = parseInt(card.dataset.pid);
  const p = window._posProducts[pid];
  if (p) selectProduct(p);
});

function filterCat(id, el) {
  document.querySelectorAll('.cat-tab').forEach(t=>t.classList.remove('active'));
  el.classList.add('active');
  currentCatId = id;
  loadProducts(document.getElementById('search-input').value, id);
}

document.getElementById('search-input').addEventListener('input', function() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => loadProducts(this.value, currentCatId), 250);
});

// ---- SKU Lookup ----
document.getElementById('sku-input').addEventListener('keydown', e => { if (e.key==='Enter') lookupSku(); });

async function lookupSku() {
  const sku = document.getElementById('sku-input').value.trim();
  if (!sku) return;
  const r = await apiFetch(SKU_URL, {sku}, 'POST');
  if (r.success) {
    const p = r.data;
    if (p.has_variants && p.variants.length) { selectProduct(p); }
    else { await addItem(p.id, null, 1); }
    document.getElementById('sku-input').value = '';
  } else { showToast('Product not found: ' + sku, 'error'); }
}

// ---- Product Modal ----
function selectProduct(pJson) {
  const p = typeof pJson === 'string' ? JSON.parse(pJson) : pJson;
  currentProduct = p;
  document.getElementById('modal-title').textContent = p.name + ' — ' + CURRENCY + p.price.toFixed(2);
  const vd = document.getElementById('modal-variants');
  if (p.has_variants && p.variants.length) {
    vd.innerHTML = '<div class="field-lbl">Select Variant</div><select id="modal-variant-sel" class="field-sm">'
      + '<option value="">Standard</option>'
      + p.variants.map(v=>`<option value="${v.id}">${v.name}${v.price_modifier>0?' (+'+CURRENCY+v.price_modifier.toFixed(2)+')':''} [Stock:${v.quantity}]</option>`).join('')
      + '</select>';
  } else { vd.innerHTML = ''; }
  document.getElementById('modal-qty').value = 1;
  document.getElementById('variant-modal').classList.add('show');
}

function closeModal() { document.getElementById('variant-modal').classList.remove('show'); }

async function addFromModal() {
  if (!currentProduct) return;
  const qty = parseInt(document.getElementById('modal-qty').value) || 1;
  const sel = document.getElementById('modal-variant-sel');
  const varId = sel ? sel.value || null : null;
  await addItem(currentProduct.id, varId, qty);
  closeModal();
}

// ---- Cart Actions ----
async function addItem(productId, variantId, qty) {
  const r = await apiFetch(ADD_URL, {product_id:productId, variant_id:variantId, quantity:qty}, 'POST');
  if (r.success) { cartData = r; renderCart(); showToast('Added to order', 'success'); }
  else showToast(r.message || 'Error adding item', 'error');
}

async function removeItem(cartKey) {
  const r = await apiFetch(REMOVE_URL, {cart_key: cartKey}, 'POST');
  if (r.success) { cartData = r; renderCart(); }
}

async function updateQty(cartKey, qty) {
  if (qty < 1) { removeItem(cartKey); return; }
  const r = await apiFetch(UPDATE_URL, {cart_key: cartKey, quantity: qty}, 'POST');
  if (r.success) { cartData = r; renderCart(); }
  else showToast(r.message, 'error');
}

async function clearCart() {
  if (!cartData.cart.length) return;
  if (!confirm('Clear all items?')) return;
  const r = await apiFetch(CLEAR_URL, {}, 'POST');
  if (r.success) { cartData = {cart:[],subtotal:0,tax_rate:0,tax_amount:0,total:0,currency:CURRENCY}; renderCart(); }
}

async function loadCart() {
  const r = await fetch(CART_URL).then(x=>x.json());
  if (r.success) { cartData = r; renderCart(); }
}

// ---- Render Cart ----
// NOTE: we write to #cart-list (child of #cart-items) so #empty-msg is NEVER
// destroyed by an innerHTML swap — that was the root-cause bug.
function renderCart() {
  const emptyMsg  = document.getElementById('empty-msg');
  const cartList  = document.getElementById('cart-list');

  if (!cartData.cart || !cartData.cart.length) {
    emptyMsg.style.display = '';
    cartList.innerHTML = '';
    updateTotals(0, 0, 0);
    return;
  }

  emptyMsg.style.display = 'none';
  cartList.innerHTML = cartData.cart.map(item => {
    const lineTotal = (item.quantity * item.unit_price).toFixed(2);
    return `<div class="cart-item">
      <div class="cart-item-info">
        <div class="cart-item-name">${item.product_name}</div>
        <div class="cart-item-sub">${item.variant_name || item.sku || ''}</div>
        <div class="qty-ctrl">
          <button class="qty-btn" onclick="updateQty('${item.cart_key}',${item.quantity - 1})">&#8722;</button>
          <span class="qty-val">${item.quantity}</span>
          <button class="qty-btn" onclick="updateQty('${item.cart_key}',${item.quantity + 1})">+</button>
          <span style="font-size:11px;color:#94a3b8;margin-left:4px">@ ${CURRENCY}${parseFloat(item.unit_price).toFixed(2)}</span>
        </div>
      </div>
      <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px">
        <span class="cart-item-price">${CURRENCY}${lineTotal}</span>
        <button class="remove-btn" onclick="removeItem('${item.cart_key}')" title="Remove"><i class="fas fa-times-circle"></i></button>
      </div>
    </div>`;
  }).join('');
  updateTotals(cartData.subtotal, cartData.tax_amount, cartData.total);
}

// ---- Totals & Discount ----
function recalc() {
  const dv = parseFloat(document.getElementById('discount-val').value)||0;
  const dt = document.getElementById('discount-type').value;
  let disc = dt==='percentage' ? cartData.subtotal*dv/100 : dv;
  disc = Math.min(disc, cartData.subtotal);
  const afterDisc = cartData.subtotal - disc;
  const tax = afterDisc * cartData.tax_rate / 100;
  const total = afterDisc + tax;
  updateTotals(cartData.subtotal, tax, total);
}

function updateTotals(sub, tax, total) {
  document.getElementById('disp-subtotal').textContent = CURRENCY+parseFloat(sub).toFixed(2);
  const taxRow = document.getElementById('disp-tax');
  if (taxRow) taxRow.textContent = CURRENCY+parseFloat(tax).toFixed(2);
  document.getElementById('disp-total').textContent = CURRENCY+parseFloat(total).toFixed(2);
  // charge-amt lives inside the button and may be null when button is in Processing state
  const chargeAmt = document.getElementById('charge-amt');
  if (chargeAmt) chargeAmt.textContent = CURRENCY+parseFloat(total).toFixed(2);
  calcChange();
}

function calcChange() {
  const totalStr = document.getElementById('disp-total').textContent.replace(CURRENCY,'').replace(/,/g,'');
  const total = parseFloat(totalStr)||0;
  const tendered = parseFloat(document.getElementById('cash-tendered').value)||0;
  const change = Math.max(0, tendered - total);
  document.getElementById('disp-change').textContent = CURRENCY+change.toFixed(2);
}

// ---- Payment (cash only) ----
// selectPay kept as stub in case it's called from anywhere else
function selectPay(method) { currentPay = 'cash'; }

// ---- Checkout ----
async function processCheckout() {
  if (!cartData.cart || !cartData.cart.length) { showToast('Cart is empty', 'error'); return; }

  const totalStr = document.getElementById('disp-total').textContent.replace(CURRENCY, '').replace(/,/g, '');
  const total    = parseFloat(totalStr) || 0;
  const tendered = parseFloat(document.getElementById('cash-tendered').value) || 0;
  if (tendered < total) { showToast('Cash tendered is less than total', 'error'); return; }

  const btn = document.getElementById('charge-btn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

  let navigating = false; // track if we are navigating away (so finally doesn't re-enable)

  try {
    const dv = parseFloat(document.getElementById('discount-val').value) || 0;
    const body = {
      payment_method: 'cash',
      customer_name:  document.getElementById('customer-name').value,
      discount:       dv,
      discount_type:  document.getElementById('discount-type').value,
      cash_tendered:  tendered,
      notes:          null,
    };

    const r = await apiFetch(CHECKOUT_URL, body, 'POST');

    if (r && r.success) {
      // Clear cart UI immediately
      cartData = {cart:[],subtotal:0,tax_rate:cartData.tax_rate||0,tax_amount:0,total:0,currency:CURRENCY};
      renderCart();
      document.getElementById('customer-name').value = '';
      document.getElementById('discount-val').value  = '0';
      document.getElementById('cash-tendered').value = '';
      calcChange();

      showToast('Sale complete! ' + r.transaction_number, 'success');
      btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Opening Receipt...';

      if (r.receipt_url) {
        navigating = true;
        setTimeout(() => { window.location.href = r.receipt_url; }, 800);
      }
    } else {
      showToast((r && r.message) || 'Checkout failed. Please try again.', 'error');
    }
  } catch (err) {
    console.error('processCheckout error:', err);
    showToast('An unexpected error occurred. Please try again.', 'error');
  } finally {
    // Always re-enable the button unless we are navigating away
    if (!navigating) {
      btn.disabled = false;
      const dispTotal = document.getElementById('disp-total');
      btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Charge <span id="charge-amt">' +
        (dispTotal ? dispTotal.textContent : CURRENCY + '0.00') + '</span>';
    }
  }
}

// ---- Helpers ----
async function apiFetch(url, data={}, method='POST') {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), 30000); // 30s timeout
  try {
    const opts = {
      method,
      signal: controller.signal,
      headers: {'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'}
    };
    if (method!=='GET') opts.body = JSON.stringify(data);
    const res = await fetch(url, opts);
    clearTimeout(timer);
    return await res.json();
  } catch(e) {
    clearTimeout(timer);
    if (e.name === 'AbortError') return {success:false, message:'Request timed out. Please try again.'};
    return {success:false, message:'Network error'};
  }
}

function showToast(msg, type='success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast '+type+' show';
  setTimeout(()=>t.classList.remove('show'), 3000);
}

// Close modal on backdrop click
document.getElementById('variant-modal').addEventListener('click', function(e){if(e.target===this)closeModal();});

init();

// Fix: when user navigates back from the receipt page, the browser restores
// this page from the back-forward cache (bfcache). The charge button can be
// frozen in "Processing..." or "Opening Receipt..." state. Reset it.
window.addEventListener('pageshow', function(event) {
  if (event.persisted) {
    // Page was restored from bfcache — reinitialise completely
    const btn = document.getElementById('charge-btn');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Charge <span id="charge-amt">' + CURRENCY + '0.00</span>';
    init(); // reload cart + products fresh from server
  }
});
</script>
@endsection
