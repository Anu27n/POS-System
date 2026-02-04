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
        Schema::create('repair_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_customer_id')->nullable()->constrained()->onDelete('set null');
            
            // Ticket identification
            $table->string('ticket_number')->unique();
            
            // Device information
            $table->enum('device_type', ['phone', 'tablet', 'laptop', 'watch', 'gaming_console', 'other'])->default('phone');
            $table->string('device_brand')->nullable();
            $table->string('device_model')->nullable();
            $table->string('imei_serial')->nullable();
            $table->string('device_color')->nullable();
            $table->string('device_password')->nullable(); // Encrypted
            $table->json('device_accessories')->nullable(); // charger, case, etc.
            
            // Issue and repair details
            $table->text('issue_description');
            $table->text('diagnosis_notes')->nullable();
            $table->text('repair_notes')->nullable();
            $table->text('internal_notes')->nullable(); // Staff-only notes
            
            // Status and workflow
            $table->enum('status', [
                'received',
                'diagnosed',
                'waiting_approval',
                'waiting_parts',
                'in_progress',
                'repaired',
                'ready_pickup',
                'delivered',
                'cancelled',
                'unrepairable'
            ])->default('received');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Assignment
            $table->foreignId('assigned_technician_id')->nullable()->constrained('staff')->onDelete('set null');
            $table->foreignId('received_by_id')->nullable()->constrained('staff')->onDelete('set null');
            
            // Pricing
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('final_cost', 10, 2)->nullable();
            $table->decimal('advance_paid', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            
            // Dates
            $table->timestamp('expected_delivery_at')->nullable();
            $table->timestamp('diagnosed_at')->nullable();
            $table->timestamp('repair_started_at')->nullable();
            $table->timestamp('repaired_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            // Warranty
            $table->integer('warranty_days')->default(0);
            $table->date('warranty_until')->nullable();
            
            // Invoice/Order reference
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'assigned_technician_id']);
            $table->index(['store_id', 'expected_delivery_at']);
            $table->index('ticket_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_jobs');
    }
};
