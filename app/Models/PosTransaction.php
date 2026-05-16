<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosTransaction extends Model
{
    protected $fillable = [
        'user_id', 'transaction_number', 'subtotal', 'tax', 'discount',
        'total', 'payment_method', 'status', 'notes', 'reference_number',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PosTransactionItem::class);
    }

    public function generateTransactionNumber()
    {
        $date = now()->format('Ymd');
        $count = static::where('transaction_number', 'like', 'POS' . $date . '%')
            ->count() + 1;
        return 'POS' . $date . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
