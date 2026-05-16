<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .receipt {
            max-width: 80mm;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .store-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .store-info {
            font-size: 9px;
            color: #666;
        }
        .receipt-number {
            font-size: 10px;
            margin-top: 8px;
            border-top: 1px solid #000;
            padding-top: 8px;
        }
        .info-section {
            font-size: 10px;
            margin: 10px 0;
            line-height: 1.4;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 70px;
        }
        table {
            width: 100%;
            margin: 15px 0;
            border-collapse: collapse;
            font-size: 10px;
        }
        table thead {
            border-top: 1px solid #000;
            border-bottom: 2px solid #000;
        }
        table th {
            text-align: left;
            padding: 5px 2px;
            font-weight: bold;
        }
        table td {
            padding: 3px 2px;
            border-bottom: 1px dotted #ccc;
        }
        .qty {
            text-align: center;
            width: 30px;
        }
        .price {
            text-align: right;
            width: 50px;
        }
        .total-row {
            border-bottom: 1px solid #000;
            font-weight: bold;
            padding-top: 5px;
        }
        .summary {
            margin: 10px 0;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
        }
        .summary-line {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 10px;
        }
        .summary-label {
            flex: 1;
        }
        .summary-amount {
            text-align: right;
            min-width: 60px;
            font-weight: bold;
        }
        .total-amount {
            font-size: 12px;
            font-weight: bold;
            margin: 8px 0;
            padding: 5px 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 9px;
            color: #666;
            line-height: 1.3;
        }
        .barcode {
            text-align: center;
            margin: 10px 0;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="store-name">QueenBuilders IMS</div>
            <div class="store-info">Hardware & Construction Supplies</div>
            <div class="receipt-number">
                Receipt #{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <!-- Transaction Info -->
        <div class="info-section">
            <div><span class="info-label">Date:</span> {{ $transaction->transaction_date->format('M d, Y') }}</div>
            <div><span class="info-label">Time:</span> {{ $transaction->transaction_date->format('H:i A') }}</div>
            <div><span class="info-label">Cashier:</span> {{ $transaction->user->name }}</div>
            <div><span class="info-label">Payment:</span> {{ ucfirst($transaction->payment_method) }}</div>
            @if($transaction->customer_name)
                <div><span class="info-label">Customer:</span> {{ $transaction->customer_name }}</div>
            @endif
        </div>

        <!-- Items -->
        <table>
            <thead>
                <tr>
                    <th style="width: 100px;">Product</th>
                    <th class="qty">Qty</th>
                    <th class="price">Price</th>
                    <th class="price" style="width: 50px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->items as $item)
                    <tr>
                        <td>
                            <div style="font-weight: bold;">{{ $item->product->name }}</div>
                            @if($item->variant)
                                <div style="font-size: 9px; color: #666;">{{ $item->variant->type }}: {{ $item->variant->value }}</div>
                            @endif
                        </td>
                        <td class="qty">{{ $item->quantity }}</td>
                        <td class="price">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="price">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-line">
                <span class="summary-label">Subtotal:</span>
                <span class="summary-amount">₱ {{ number_format($transaction->subtotal, 2) }}</span>
            </div>
            @if($transaction->discount_amount > 0)
                <div class="summary-line">
                    <span class="summary-label">Discount:</span>
                    <span class="summary-amount" style="color: red;">-₱ {{ number_format($transaction->discount_amount, 2) }}</span>
                </div>
            @endif
        </div>

        <!-- Total -->
        <div class="total-amount">
            <div style="text-align: right;">
                TOTAL: ₱ {{ number_format($transaction->total_amount, 2) }}
            </div>
        </div>

        <!-- Notes -->
        @if($transaction->notes)
            <div class="info-section" style="border-top: 1px solid #000; padding-top: 5px;">
                <div style="font-weight: bold; font-size: 9px; margin-bottom: 3px;">Notes:</div>
                <div style="font-size: 9px;">{{ $transaction->notes }}</div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div style="margin-top: 10px;">Thank you for your purchase!</div>
            <div>Please retain this receipt for your records</div>
            <div style="margin-top: 8px;">For inquiries, contact our customer service</div>
        </div>
    </div>
</body>
</html>
