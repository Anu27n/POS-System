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
        // Store-level tax settings
        Schema::create('store_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "CGST", "SGST", "GST", "VAT"
            $table->decimal('percentage', 5, 2); // e.g., 9.00 for 9%
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Store tax settings
        Schema::create('store_tax_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->unique()->constrained()->onDelete('cascade');
            $table->boolean('taxes_enabled')->default(false);
            $table->enum('tax_type', ['item_level', 'order_level'])->default('order_level');
            $table->string('tax_number')->nullable(); // GST/VAT registration number
            $table->boolean('show_tax_on_receipt')->default(true);
            $table->boolean('tax_inclusive_pricing')->default(false);
            $table->timestamps();
        });

        // Order tax breakdown
        Schema::create('order_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_tax_id')->nullable()->constrained()->onDelete('set null');
            $table->string('tax_name');
            $table->decimal('tax_percentage', 5, 2);
            $table->decimal('taxable_amount', 10, 2);
            $table->decimal('tax_amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_taxes');
        Schema::dropIfExists('store_tax_settings');
        Schema::dropIfExists('store_taxes');
    }
};
