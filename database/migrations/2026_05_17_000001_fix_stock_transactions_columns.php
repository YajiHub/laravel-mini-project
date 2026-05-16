<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            // Add missing columns
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete()->after('product_id');
            $table->string('reference')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn(['product_variant_id', 'reference']);
        });
    }
};
