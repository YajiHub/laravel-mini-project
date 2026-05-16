<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix pos_transactions: add missing columns the controller expects
        Schema::table('pos_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_transactions', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('reference_number');
            }
            if (!Schema::hasColumn('pos_transactions', 'transaction_date')) {
                $table->timestamp('transaction_date')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('pos_transactions', 'cash_tendered')) {
                $table->decimal('cash_tendered', 12, 2)->nullable()->after('transaction_date');
            }
            if (!Schema::hasColumn('pos_transactions', 'change_amount')) {
                $table->decimal('change_amount', 12, 2)->nullable()->after('cash_tendered');
            }
            if (!Schema::hasColumn('pos_transactions', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('change_amount');
            }
            if (!Schema::hasColumn('pos_transactions', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_rate');
            }
        });

        // Fix pos_transaction_items: add snapshot columns
        Schema::table('pos_transaction_items', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_transaction_items', 'product_name')) {
                $table->string('product_name')->after('product_variant_id');
            }
            if (!Schema::hasColumn('pos_transaction_items', 'sku')) {
                $table->string('sku')->nullable()->after('product_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name', 'transaction_date', 'cash_tendered',
                'change_amount', 'tax_rate', 'tax_amount',
            ]);
        });

        Schema::table('pos_transaction_items', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'sku']);
        });
    }
};
