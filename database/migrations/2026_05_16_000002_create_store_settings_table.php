<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('label');
            $table->string('type')->default('text'); // text, number, boolean, select
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        DB::table('store_settings')->insert([
            [
                'key'         => 'store_name',
                'value'       => 'QueenBuilders Hardware',
                'label'       => 'Store Name',
                'type'        => 'text',
                'description' => 'The name shown on receipts',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'store_address',
                'value'       => 'Hardware & Construction Supplies',
                'label'       => 'Store Address / Tagline',
                'type'        => 'text',
                'description' => 'Address or tagline shown on receipts',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'tax_rate',
                'value'       => '0',
                'label'       => 'Tax Rate (%)',
                'type'        => 'number',
                'description' => 'VAT / tax percentage applied to all sales (0 = no tax)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'tax_label',
                'value'       => 'VAT',
                'label'       => 'Tax Label',
                'type'        => 'text',
                'description' => 'Label displayed for tax on receipts (e.g. VAT, GST)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'currency_symbol',
                'value'       => '₱',
                'label'       => 'Currency Symbol',
                'type'        => 'text',
                'description' => 'Currency symbol shown on receipts and POS',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'receipt_footer',
                'value'       => 'Thank you for your purchase!',
                'label'       => 'Receipt Footer Message',
                'type'        => 'text',
                'description' => 'Message printed at the bottom of receipts',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
