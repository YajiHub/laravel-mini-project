<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { margin: 15mm 12mm; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 8pt; color: #1a1a2e; }
  h1 { font-size: 14pt; color: #1e3a5f; margin: 0 0 2px; }
  .subtitle { color: #6b7280; font-size: 8pt; margin-bottom: 12px; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  thead th { background: #1e3a5f; color: #fff; padding: 6px 7px; text-align: left; font-size: 7.5pt; }
  tbody tr:nth-child(even) { background: #f8fafc; }
  tbody td { padding: 4px 7px; border-bottom: 1px solid #e2e8f0; font-size: 7.5pt; vertical-align: middle; }
  .text-right { text-align: right; }
  .badge-green { background: #dcfce7; color: #166534; padding: 1px 6px; border-radius: 9999px; font-size: 7pt; font-weight: bold; }
  .badge-red { background: #fee2e2; color: #991b1b; padding: 1px 6px; border-radius: 9999px; font-size: 7pt; font-weight: bold; }
  .badge-gray { background: #f3f4f6; color: #374151; padding: 1px 6px; border-radius: 9999px; font-size: 7pt; font-weight: bold; }
  .footer { margin-top: 14px; text-align: center; font-size: 7pt; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 6px; }
  .summary { display: flex; gap: 20px; margin-bottom: 10px; }
  .summary-box { background: #eff6ff; border-radius: 6px; padding: 6px 12px; }
  .summary-box .val { font-size: 13pt; font-weight: bold; color: #1d4ed8; }
  .summary-box .lbl { font-size: 7pt; color: #6b7280; }
</style>
</head>
<body>

<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px">
  <div>
    <h1>Inventory Report</h1>
    <p class="subtitle">QueenBuilders Hardware &mdash; Generated: {{ $generated_at }}</p>
  </div>
  <div style="text-align:right;font-size:7.5pt;color:#6b7280">
    Total Items: <strong>{{ $products->count() }}</strong>
  </div>
</div>

<table>
  <thead>
    <tr>
      <th>#</th>
      <th>SKU</th>
      <th>Product Name</th>
      <th>Category</th>
      <th>Supplier</th>
      <th class="text-right">Price (₱)</th>
      <th class="text-right">Qty</th>
      <th>Unit</th>
      <th class="text-right">Reorder Lvl</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach($products as $i => $p)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td style="font-family:monospace">{{ $p->sku }}</td>
      <td><strong>{{ $p->name }}</strong></td>
      <td>{{ $p->category?->name ?? '—' }}</td>
      <td>{{ $p->supplier?->name ?? '—' }}</td>
      <td class="text-right">{{ number_format($p->price, 2) }}</td>
      <td class="text-right">
        @if($p->quantity == 0)
          <span class="badge-gray">0</span>
        @elseif($p->quantity <= $p->low_stock_threshold)
          <span class="badge-red">{{ $p->quantity }}</span>
        @else
          <span class="badge-green">{{ $p->quantity }}</span>
        @endif
      </td>
      <td>{{ $p->unit }}</td>
      <td class="text-right">{{ $p->low_stock_threshold }}</td>
      <td>
        @if($p->is_active)
          <span class="badge-green">Active</span>
        @else
          <span class="badge-gray">Inactive</span>
        @endif
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="footer">
  QueenBuilders Hardware IMS &mdash; Inventory Report &nbsp;|&nbsp; Prepared by Montecillo &amp; Salapang
</div>

</body>
</html>
