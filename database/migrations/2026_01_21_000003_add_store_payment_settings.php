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
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('enable_online_payment')->default(false)->after('status');
            $table->boolean('enable_counter_payment')->default(true)->after('enable_online_payment');
            $table->string('razorpay_key_id')->nullable()->after('enable_counter_payment');
            $table->text('razorpay_key_secret')->nullable()->after('razorpay_key_id');
            $table->boolean('razorpay_enabled')->default(false)->after('razorpay_key_secret');
            $table->string('stripe_publishable_key')->nullable()->after('razorpay_enabled');
            $table->text('stripe_secret_key')->nullable()->after('stripe_publishable_key');
            $table->boolean('stripe_enabled')->default(false)->after('stripe_secret_key');
            $table->boolean('is_test_mode')->default(true)->after('stripe_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'enable_online_payment',
                'enable_counter_payment',
                'razorpay_key_id',
                'razorpay_key_secret',
                'razorpay_enabled',
                'stripe_publishable_key',
                'stripe_secret_key',
                'stripe_enabled',
                'is_test_mode',
            ]);
        });
    }
};
