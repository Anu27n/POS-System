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
        Schema::create('repair_job_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_job_id')->constrained()->onDelete('cascade');
            
            $table->string('old_status')->nullable();
            $table->string('new_status');
            
            // Who changed the status
            $table->foreignId('changed_by_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->text('notes')->nullable();
            $table->boolean('notify_customer')->default(false);
            $table->timestamp('notification_sent_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('repair_job_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_job_status_logs');
    }
};
