<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode')->nullable()->after('sku');
            $table->decimal('cost_price', 10, 2)->nullable()->after('compare_price');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('cost_price');
            $table->string('sizes')->nullable()->after('gallery');
            $table->string('colors')->nullable()->after('sizes');
            $table->string('unit')->nullable()->after('colors');
            $table->decimal('weight', 8, 2)->nullable()->after('unit');
            $table->boolean('is_featured')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'barcode',
                'cost_price',
                'tax_rate',
                'sizes',
                'colors',
                'unit',
                'weight',
                'is_featured'
            ]);
        });
    }
};
