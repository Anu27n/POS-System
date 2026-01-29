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
        // Cash register sessions
        Schema::create('cash_register_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->nullable()->constrained('users')->onDelete('set null'); // Staff/owner who opened
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // For backwards compatibility
            $table->string('staff_name')->nullable();
            $table->decimal('opening_cash', 10, 2);
            $table->decimal('closing_cash', 10, 2)->nullable();
            $table->decimal('expected_cash', 10, 2)->nullable(); // Calculated based on transactions
            $table->decimal('cash_difference', 10, 2)->nullable(); // Closing - Expected
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_cash_sales', 10, 2)->default(0);
            $table->decimal('total_card_sales', 10, 2)->default(0);
            $table->decimal('total_upi_sales', 10, 2)->default(0);
            $table->decimal('total_other_sales', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('closing_notes')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'closed_at']);
            $table->index(['store_id', 'opened_at']);
        });

        // Individual cash transactions log
        Schema::create('cash_register_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['sale', 'refund', 'cash_in', 'cash_out']);
            $table->string('payment_method'); // cash, card, upi, etc.
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_register_transactions');
        Schema::dropIfExists('cash_register_sessions');
    }
};
