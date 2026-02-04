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
        Schema::create('repair_job_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_job_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Spare part
            
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            
            // Who added this part
            $table->foreignId('added_by_id')->nullable()->constrained('staff')->onDelete('set null');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('repair_job_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_job_parts');
    }
};
