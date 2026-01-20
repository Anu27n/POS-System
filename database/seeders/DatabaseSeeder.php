<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Store;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@pos.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create Store Owner
        $storeOwner = User::create([
            'name' => 'Store Owner',
            'email' => 'store@pos.com',
            'password' => Hash::make('password'),
            'role' => 'store_owner',
        ]);

        // Create a Store
        $store = Store::create([
            'name' => 'Demo Coffee Shop',
            'slug' => 'demo-coffee-shop',
            'description' => 'A demo coffee shop for testing',
            'address' => '123 Main Street, City',
            'phone' => '555-1234',
            'email' => 'store@pos.com',
            'user_id' => $storeOwner->id,
            'status' => 'active',
            'tax_rate' => 10.00,
            'currency' => 'INR',
        ]);

        // Create Categories
        $drinks = Category::create([
            'name' => 'Drinks',
            'slug' => 'drinks',
            'store_id' => $store->id,
        ]);

        $food = Category::create([
            'name' => 'Food',
            'slug' => 'food',
            'store_id' => $store->id,
        ]);

        // Create Products
        Product::create([
            'name' => 'Espresso',
            'slug' => 'espresso',
            'description' => 'Strong Italian coffee',
            'price' => 3.50,
            'stock_quantity' => 100,
            'store_id' => $store->id,
            'category_id' => $drinks->id,
            'status' => 'available',
        ]);

        Product::create([
            'name' => 'Cappuccino',
            'slug' => 'cappuccino',
            'description' => 'Espresso with steamed milk foam',
            'price' => 4.50,
            'stock_quantity' => 100,
            'store_id' => $store->id,
            'category_id' => $drinks->id,
            'status' => 'available',
        ]);

        Product::create([
            'name' => 'Latte',
            'slug' => 'latte',
            'description' => 'Espresso with lots of steamed milk',
            'price' => 4.00,
            'stock_quantity' => 100,
            'store_id' => $store->id,
            'category_id' => $drinks->id,
            'status' => 'available',
        ]);

        Product::create([
            'name' => 'Croissant',
            'slug' => 'croissant',
            'description' => 'Fresh butter croissant',
            'price' => 2.50,
            'stock_quantity' => 50,
            'store_id' => $store->id,
            'category_id' => $food->id,
            'status' => 'available',
        ]);

        Product::create([
            'name' => 'Sandwich',
            'slug' => 'sandwich',
            'description' => 'Ham and cheese sandwich',
            'price' => 6.00,
            'stock_quantity' => 30,
            'store_id' => $store->id,
            'category_id' => $food->id,
            'status' => 'available',
        ]);

        // Create a Customer User
        User::create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        echo "✅ Database seeded successfully!\n";
        echo "\n📋 TEST ACCOUNTS:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Admin:       admin@pos.com / password\n";
        echo "Store Owner: store@pos.com / password\n";
        echo "Customer:    customer@test.com / password\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    }
}
