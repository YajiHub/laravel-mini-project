<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { margin: 20mm 15mm; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1a1a2e; }
  h1 { font-size: 16pt; color: #1e3a5f; margin: 0 0 4px; }
  .subtitle { color: #6b7280; font-size: 9pt; margin-bottom: 16px; }
  .meta { display: flex; justify-content: space-between; margin-bottom: 12px; }
  .badge { background: #eff6ff; color: #1d4ed8; padding: 2px 8px; border-radius: 4px; font-size: 8pt; font-weight: bold; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  thead th { background: #1e3a5f; color: #fff; padding: 7px 8px; text-align: left; font-size: 8pt; }
  tbody tr:nth-child(even) { background: #f8fafc; }
  tbody td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; font-size: 8pt; }
  .text-right { text-align: right; }
  .totals { margin-top: 16px; background: #1e3a5f; color: #fff; padding: 10px 14px; border-radius: 6px; }
  .totals table { margin: 0; }
  .totals td { color: #fff; padding: 3px 0; border: none; font-size: 9pt; }
  .footer { margin-top: 20px; text-align: center; font-size: 7pt; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
</style>
</head>
<body>

<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px">
  <div>
    <h1>Sales Summary Report</h1>
    <p class="subtitle">QueenBuilders Hardware | Period: {{ $from }} — {{ $to }}</p>
  </div>
  <div>
    <div class="badge">{{ $transactions->count() }} Transactions</div>
    <p style="font-size:7pt;color:#9ca3af;margin-top:4px">Generated: {{ $generated_at }}</p>
  </div>
</div>

<table>
  <thead>
    <tr>
      <th>Transaction #</th>
      <th>Date</th>
      <th>Cashier</th>
      <th>Customer</th>
      <th class="text-right">Subtotal</th>
      <th class="text-right">Discount</th>
      <th class="text-right">Tax</th>
      <th class="text-right">Total</th>
    </tr>
  </thead>
  <tbody>
    @foreach($transactions as $t)
    <tr>
      <td style="font-family:monospace">{{ $t->transaction_number }}</td>
      <td>{{ $t->transaction_date?->format('M d, Y g:i A') }}</td>
      <td>{{ $t->user?->name ?? '—' }}</td>
      <td>{{ $t->customer_name ?? 'Walk-in' }}</td>
      <td class="text-right">₱{{ number_format($t->subtotal, 2) }}</td>
      <td class="text-right">₱{{ number_format($t->discount, 2) }}</td>
      <td class="text-right">₱{{ number_format($t->tax_amount, 2) }}</td>
      <td class="text-right" style="font-weight:bold">₱{{ number_format($t->total, 2) }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="totals" style="margin-top:16px">
  <table style="width:280px;margin-left:auto">
    <tr><td>Total Discount:</td><td class="text-right">₱{{ number_format($totalDiscount, 2) }}</td></tr>
    <tr><td>Total Tax Collected:</td><td class="text-right">₱{{ number_format($totalTax, 2) }}</td></tr>
    <tr><td style="font-weight:bold;font-size:10pt">GROSS REVENUE:</td><td class="text-right" style="font-weight:bold;font-size:10pt">₱{{ number_format($totalRevenue, 2) }}</td></tr>
  </table>
</div>

<div class="footer">
  QueenBuilders Hardware IMS — Confidential Report &nbsp;|&nbsp; Prepared by Montecillo &amp; Salapang
</div>

</body>
</html>
