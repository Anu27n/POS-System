<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PlanFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'category',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Available feature categories
    const CATEGORIES = [
        'billing' => 'Billing & POS',
        'inventory' => 'Inventory Management',
        'reports' => 'Reports & Analytics',
        'customers' => 'Customer Management',
        'staff' => 'Staff Management',
        'integrations' => 'Integrations',
        'advanced' => 'Advanced Features',
    ];

    // Default features with their slugs
    const DEFAULT_FEATURES = [
        ['name' => 'POS Terminal', 'slug' => 'pos_terminal', 'category' => 'billing', 'description' => 'Point of sale terminal for processing orders'],
        ['name' => 'QR Code Ordering', 'slug' => 'qr_ordering', 'category' => 'billing', 'description' => 'Allow customers to order via QR code'],
        ['name' => 'Multiple Payment Methods', 'slug' => 'multiple_payments', 'category' => 'billing', 'description' => 'Accept cash, card, UPI payments'],
        ['name' => 'Product Management', 'slug' => 'product_management', 'category' => 'inventory', 'description' => 'Add, edit, and manage products'],
        ['name' => 'Category Management', 'slug' => 'category_management', 'category' => 'inventory', 'description' => 'Organize products in categories'],
        ['name' => 'Stock Tracking', 'slug' => 'stock_tracking', 'category' => 'inventory', 'description' => 'Track inventory levels and low stock alerts'],
        ['name' => 'Sales Reports', 'slug' => 'sales_reports', 'category' => 'reports', 'description' => 'View detailed sales analytics'],
        ['name' => 'Tax Reports', 'slug' => 'tax_reports', 'category' => 'reports', 'description' => 'Generate GST/tax reports'],
        ['name' => 'Inventory Reports', 'slug' => 'inventory_reports', 'category' => 'reports', 'description' => 'Stock movement and valuation reports'],
        ['name' => 'Customer Database', 'slug' => 'customer_database', 'category' => 'customers', 'description' => 'Maintain customer records'],
        ['name' => 'Customer Loyalty', 'slug' => 'customer_loyalty', 'category' => 'customers', 'description' => 'Loyalty points and rewards'],
        ['name' => 'Staff Accounts', 'slug' => 'staff_accounts', 'category' => 'staff', 'description' => 'Create staff accounts with permissions'],
        ['name' => 'Cash Register', 'slug' => 'cash_register', 'category' => 'staff', 'description' => 'Daily cash register management'],
        ['name' => 'Staff Reports', 'slug' => 'staff_reports', 'category' => 'staff', 'description' => 'Track staff performance'],
        ['name' => 'Multi-Tax Support', 'slug' => 'multi_tax', 'category' => 'advanced', 'description' => 'Configure multiple GST taxes'],
        ['name' => 'Discount Management', 'slug' => 'discount_management', 'category' => 'advanced', 'description' => 'Create and apply discounts'],
        ['name' => 'Online Store', 'slug' => 'online_store', 'category' => 'integrations', 'description' => 'Public store page for online orders'],
        ['name' => 'Payment Gateway', 'slug' => 'payment_gateway', 'category' => 'integrations', 'description' => 'Razorpay/Stripe integration'],
        ['name' => 'Email Notifications', 'slug' => 'email_notifications', 'category' => 'integrations', 'description' => 'Send order confirmations via email'],
        ['name' => 'Export Data', 'slug' => 'export_data', 'category' => 'advanced', 'description' => 'Export reports to CSV/Excel'],
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($feature) {
            if (empty($feature->slug)) {
                $feature->slug = Str::slug($feature->name, '_');
            }
        });
    }

    /**
     * Scope for active features
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get features grouped by category
     */
    public static function getGroupedFeatures()
    {
        return static::active()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');
    }
}
