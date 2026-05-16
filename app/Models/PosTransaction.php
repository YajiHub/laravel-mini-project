<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_number',
        'customer_name',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount',
        'total',
        'payment_method',
        'cash_tendered',
        'change_amount',
        'reference_number',
        'status',
        'notes',
        'transaction_date',
    ];

    protected $casts = [
        'subtotal'       => 'decimal:2',
        'tax_rate'       => 'decimal:2',
        'tax_amount'     => 'decimal:2',
        'discount'       => 'decimal:2',
        'total'          => 'decimal:2',
        'cash_tendered'  => 'decimal:2',
        'change_amount'  => 'decimal:2',
        'transaction_date' => 'datetime',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PosTransactionItem::class);
    }

    public static function generateTransactionNumber(): string
    {
        $date  = now()->format('Ymd');
        $count = static::where('transaction_number', 'like', 'POS-' . $date . '-%')->count() + 1;
        return 'POS-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function getFormattedTotalAttribute(): string
    {
        $symbol = \App\Models\StoreSetting::get('currency_symbol', '₱');
        return $symbol . number_format((float) $this->total, 2);
    }
}
