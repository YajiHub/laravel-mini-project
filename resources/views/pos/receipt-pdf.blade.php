<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 11px;
    color: #1a1a1a;
    background: #fff;
    width: 100%;
}
.receipt { width: 100%; padding: 10px 12px; }

/* Header */
.header { text-align: center; padding-bottom: 8px; margin-bottom: 8px; border-bottom: 1px dashed #bbb; }
.store-name { font-size: 15px; font-weight: 700; color: #0f172a; }
.store-sub  { font-size: 9px; color: #64748b; margin-top: 2px; }
.store-receipt-label { font-size: 9px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: #94a3b8; margin-top: 5px; }

/* Void stamp */
.void-stamp {
    text-align: center; color: #dc2626; font-size: 18px; font-weight: 700;
    border: 3px solid #dc2626; padding: 3px 8px; display: table; margin: 8px auto;
    transform: rotate(-12deg);
}

/* Transaction info — use table layout for dompdf compat */
.info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
.info-table td { padding: 2px 0; font-size: 10px; vertical-align: top; }
.info-lbl { color: #64748b; width: 38%; }
.info-val { font-weight: 600; color: #0f172a; }

/* Divider */
.divider { border: none; border-top: 1px dashed #bbb; margin: 8px 0; }

/* Items table */
.items-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
.items-table th {
    font-size: 8px; text-transform: uppercase; color: #64748b;
    border-bottom: 1px solid #bbb; padding: 4px 3px; text-align: left;
}
.items-table th.r { text-align: right; }
.items-table td { font-size: 10px; padding: 4px 3px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
.items-table td.r { text-align: right; }
.item-name { font-weight: 600; }
.item-sku  { font-size: 8px; color: #94a3b8; margin-top: 1px; }

/* Totals — table layout for dompdf */
.totals-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
.totals-table td { padding: 2px 0; font-size: 11px; vertical-align: top; }
.totals-table td.lbl { color: #475569; }
.totals-table td.val { text-align: right; font-weight: 600; color: #0f172a; }
.totals-table .grand td { font-size: 13px; font-weight: 700; color: #0f172a; border-top: 2px solid #0f172a; padding-top: 5px; margin-top: 4px; }
.totals-table .discount td { color: #dc2626; }
.totals-table .change-row td {
    background: #f0fdf4; color: #166534; font-weight: 700;
    font-size: 12px; padding: 4px 6px; border-radius: 3px;
}

/* Footer */
.footer { text-align: center; margin-top: 12px; padding-top: 10px; border-top: 1px dashed #bbb; }
.footer p { font-size: 9px; color: #64748b; margin-bottom: 2px; }
.footer .thank-you { font-size: 11px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
.footer .timestamp { font-size: 8px; color: #94a3b8; margin-top: 6px; }
</style>
</head>
<body>
<div class="receipt">

  {{-- Header --}}
  <div class="header">
    <div class="store-name">{{ $settings['store_name'] ?? 'QueenBuilders Hardware' }}</div>
    <div class="store-sub">{{ $settings['store_address'] ?? 'Hardware & Construction Supplies' }}</div>
    <div class="store-receipt-label">Official Receipt</div>
  </div>

  {{-- Void stamp --}}
  @if($transaction->status === 'voided')
  <div style="text-align:center;margin-bottom:8px">
    <div class="void-stamp">VOIDED</div>
  </div>
  @endif

  {{-- Transaction info --}}
  <table class="info-table">
    <tr>
      <td class="info-lbl">Receipt No.</td>
      <td class="info-val">{{ $transaction->transaction_number }}</td>
    </tr>
    <tr>
      <td class="info-lbl">Date</td>
      <td class="info-val">{{ $transaction->transaction_date->format('M d, Y g:i A') }}</td>
    </tr>
    <tr>
      <td class="info-lbl">Cashier</td>
      <td class="info-val">{{ $transaction->user->name }}</td>
    </tr>
    @if($transaction->customer_name)
    <tr>
      <td class="info-lbl">Customer</td>
      <td class="info-val">{{ $transaction->customer_name }}</td>
    </tr>
    @endif
    <tr>
      <td class="info-lbl">Payment</td>
      <td class="info-val">{{ ucwords(str_replace('_', ' ', $transaction->payment_method)) }}</td>
    </tr>
  </table>

  <hr class="divider">

  {{-- Items --}}
  <table class="items-table">
    <thead>
      <tr>
        <th>Item</th>
        <th class="r" style="width:28px">Qty</th>
        <th class="r" style="width:58px">Unit Price</th>
        <th class="r" style="width:62px">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($transaction->items as $item)
      <tr>
        <td>
          <div class="item-name">{{ $item->product_name }}</div>
          @if($item->variant_name)
            <div class="item-sku">{{ $item->variant_name }}</div>
          @endif
          <div class="item-sku">{{ $item->sku }}</div>
        </td>
        <td class="r">{{ $item->quantity }}</td>
        <td class="r">{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($item->unit_price, 2) }}</td>
        <td class="r">{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($item->subtotal, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <hr class="divider">

  {{-- Totals --}}
  <table class="totals-table">
    <tr>
      <td class="lbl">Subtotal</td>
      <td class="val">{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->subtotal, 2) }}</td>
    </tr>
    @if($transaction->discount > 0)
    <tr class="discount">
      <td class="lbl">Discount</td>
      <td class="val">&minus;{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->discount, 2) }}</td>
    </tr>
    @endif
    @if($transaction->tax_amount > 0)
    <tr>
      <td class="lbl">{{ $settings['tax_label'] ?? 'VAT' }} ({{ number_format($transaction->tax_rate, 0) }}%)</td>
      <td class="val">{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->tax_amount, 2) }}</td>
    </tr>
    @endif
    <tr class="grand">
      <td class="lbl" style="padding-top:5px">TOTAL</td>
      <td class="val" style="padding-top:5px">{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->total, 2) }}</td>
    </tr>
    @if($transaction->payment_method === 'cash')
    <tr>
      <td class="lbl" style="padding-top:4px">Cash Tendered</td>
      <td class="val" style="padding-top:4px">{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->cash_tendered, 2) }}</td>
    </tr>
    <tr class="change-row">
      <td class="lbl">CHANGE</td>
      <td class="val">{{ $settings['currency_symbol'] ?? '₱' }}{{ number_format($transaction->change_amount, 2) }}</td>
    </tr>
    @endif
  </table>

  {{-- Footer --}}
  <div class="footer">
    <p class="thank-you">{{ $settings['receipt_footer'] ?? 'Thank you for your purchase!' }}</p>
    <p>For concerns, please contact our store.</p>
    <p class="timestamp">Printed: {{ now()->format('M d, Y g:i A') }}</p>
  </div>

</div>
</body>
</html>
