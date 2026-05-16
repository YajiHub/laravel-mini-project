<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size:12px; color:#1a1a1a; background:#fff; width:100%; }
.receipt { width:100%; padding:16px 20px; }
.header { text-align:center; border-bottom:2px dashed #ccc; padding-bottom:12px; margin-bottom:12px; }
.store-name { font-size:18px; font-weight:700; color:#1a1a1a; }
.store-sub { font-size:10px; color:#666; margin-top:2px; }
.txn-grid { display:table; width:100%; margin-bottom:12px; }
.txn-row { display:table-row; }
.txn-lbl, .txn-val { display:table-cell; padding:2px 0; font-size:10px; }
.txn-lbl { color:#666; width:45%; }
.txn-val { font-weight:600; color:#1a1a1a; }
.divider { border:none; border-top:1px dashed #ccc; margin:10px 0; }
table { width:100%; border-collapse:collapse; margin-bottom:10px; }
th { font-size:9px; text-transform:uppercase; color:#666; border-bottom:1px solid #ccc; padding:4px 2px; text-align:left; }
th.r { text-align:right; }
td { font-size:11px; padding:4px 2px; border-bottom:1px solid #f0f0f0; vertical-align:top; }
td.r { text-align:right; }
.item-name { font-weight:600; }
.item-sub { font-size:9px; color:#666; }
.totals { margin-top:4px; }
.total-row { display:flex; justify-content:space-between; font-size:11px; margin-bottom:3px; }
.total-row.grand { font-size:14px; font-weight:700; border-top:2px solid #1a1a1a; padding-top:5px; margin-top:5px; }
.total-row.change { background:#f0fdf4; padding:4px 6px; border-radius:4px; font-weight:700; color:#166534; margin-top:4px; }
.footer { text-align:center; margin-top:14px; padding-top:10px; border-top:1px dashed #ccc; }
.footer p { font-size:10px; color:#666; margin-bottom:3px; }
.void-stamp { text-align:center; color:#dc2626; font-size:20px; font-weight:700; border:3px solid #dc2626; padding:4px 10px; display:inline-block; transform:rotate(-15deg); margin:10px auto; }
</style>
</head>
<body>
<div class="receipt">
  <div class="header">
    <div class="store-name">{{ $settings['store_name'] ?? 'QueenBuilders Hardware' }}</div>
    <div class="store-sub">{{ $settings['store_address'] ?? 'Hardware & Construction Supplies' }}</div>
  </div>

  @if($transaction->status === 'voided')
  <div style="text-align:center;margin-bottom:10px"><div class="void-stamp">VOIDED</div></div>
  @endif

  <div class="txn-grid">
    <div class="txn-row"><span class="txn-lbl">Receipt No.</span><span class="txn-val">{{ $transaction->transaction_number }}</span></div>
    <div class="txn-row"><span class="txn-lbl">Date</span><span class="txn-val">{{ $transaction->transaction_date->format('M d, Y g:i A') }}</span></div>
    <div class="txn-row"><span class="txn-lbl">Cashier</span><span class="txn-val">{{ $transaction->user->name }}</span></div>
    @if($transaction->customer_name)
    <div class="txn-row"><span class="txn-lbl">Customer</span><span class="txn-val">{{ $transaction->customer_name }}</span></div>
    @endif
    <div class="txn-row"><span class="txn-lbl">Payment</span><span class="txn-val">{{ ucwords(str_replace('_',' ',$transaction->payment_method)) }}</span></div>
    @if($transaction->reference_number)
    <div class="txn-row"><span class="txn-lbl">Reference</span><span class="txn-val">{{ $transaction->reference_number }}</span></div>
    @endif
  </div>

  <hr class="divider">

  <table>
    <thead>
      <tr>
        <th>Item</th>
        <th class="r" style="width:30px">Qty</th>
        <th class="r" style="width:55px">Price</th>
        <th class="r" style="width:60px">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($transaction->items as $item)
      <tr>
        <td>
          <div class="item-name">{{ $item->product_name }}</div>
          @if($item->variant)<div class="item-sub">{{ $item->variant->type }}: {{ $item->variant->value }}</div>@endif
          <div class="item-sub">{{ $item->sku }}</div>
        </td>
        <td class="r">{{ $item->quantity }}</td>
        <td class="r">{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($item->unit_price,2) }}</td>
        <td class="r">{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($item->subtotal,2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <hr class="divider">

  <div class="totals">
    <div class="total-row"><span>Subtotal</span><span>{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->subtotal,2) }}</span></div>
    @if($transaction->discount > 0)
    <div class="total-row" style="color:#dc2626"><span>Discount</span><span>−{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->discount,2) }}</span></div>
    @endif
    @if($transaction->tax_amount > 0)
    <div class="total-row"><span>{{ $settings['tax_label'] ?? 'VAT' }} ({{ number_format($transaction->tax_rate,2) }}%)</span><span>{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->tax_amount,2) }}</span></div>
    @endif
    <div class="total-row grand"><span>TOTAL</span><span>{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->total,2) }}</span></div>
    @if($transaction->payment_method === 'cash')
    <div class="total-row" style="margin-top:5px"><span>Cash Tendered</span><span>{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->cash_tendered,2) }}</span></div>
    <div class="total-row change"><span>CHANGE</span><span>{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->change_amount,2) }}</span></div>
    @endif
  </div>

  <div class="footer">
    <p><strong>{{ $settings['receipt_footer'] ?? 'Thank you for your purchase!' }}</strong></p>
    <p>For concerns please contact our store.</p>
    <p style="margin-top:6px;font-size:9px;color:#999">{{ now()->format('Y-m-d H:i:s') }}</p>
  </div>
</div>
</body>
</html>
