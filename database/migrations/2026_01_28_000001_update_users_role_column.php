<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration updates the role column to include 'staff' role
     * which is needed for store staff members.
     */
    public function up(): void
    {
        // Modify the enum to include 'staff'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'store_owner', 'customer', 'staff') DEFAULT 'customer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'store_owner', 'customer') DEFAULT 'customer'");
    }
};
