<?php

namespace App\Observers;

use App\Mail\NewOrderNotification;
use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        $this->sendNewOrderNotification($order);
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Check if order_status changed by comparing with original
        if ($order->wasChanged('order_status')) {
            $previousStatus = $order->getOriginal('order_status');
            $currentStatus = $order->order_status;
            
            if ($previousStatus && $previousStatus !== $currentStatus) {
                $this->sendOrderStatusNotification($order, $previousStatus);
            }
        }
    }

    /**
     * Send notification to store owner about new order
     */
    private function sendNewOrderNotification(Order $order): void
    {
        // Check if notifications are enabled
        if (!SystemSetting::get('notifications_enabled', false)) {
            return;
        }

        if (!SystemSetting::get('notify_new_order', true)) {
            return;
        }

        // Apply SMTP settings
        $this->applySmtpSettings();

        try {
            // Get store owner email
            $store = $order->store;
            if (!$store) {
                return;
            }

            $owner = $store->owner;
            if (!$owner || !$owner->email) {
                return;
            }

            // Load order relationships
            $order->load(['items', 'store', 'customer', 'storeCustomer']);

            Mail::to($owner->email)->send(new NewOrderNotification($order));
            
            \Log::info('New order notification sent', [
                'order_id' => $order->id,
                'store_owner_email' => $owner->email,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send new order notification: ' . $e->getMessage(), [
                'order_id' => $order->id,
            ]);
        }
    }

    /**
     * Send notification to customer about order status change
     */
    private function sendOrderStatusNotification(Order $order, string $previousStatus): void
    {
        // Check if notifications are enabled
        if (!SystemSetting::get('notifications_enabled', false)) {
            return;
        }

        if (!SystemSetting::get('notify_order_status', true)) {
            return;
        }

        // Apply SMTP settings
        $this->applySmtpSettings();

        try {
            // Get customer email
            $customerEmail = null;
            
            if ($order->user && $order->user->email) {
                $customerEmail = $order->user->email;
            } elseif ($order->storeCustomer && $order->storeCustomer->email) {
                $customerEmail = $order->storeCustomer->email;
            }

            if (!$customerEmail) {
                return;
            }

            // Load order relationships
            $order->load(['items', 'store']);

            Mail::to($customerEmail)->send(new OrderStatusUpdated($order, $previousStatus));
            
            \Log::info('Order status notification sent', [
                'order_id' => $order->id,
                'customer_email' => $customerEmail,
                'previous_status' => $previousStatus,
                'new_status' => $order->order_status,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send order status notification: ' . $e->getMessage(), [
                'order_id' => $order->id,
            ]);
        }
    }

    /**
     * Apply SMTP settings from database to config
     */
    private function applySmtpSettings(): void
    {
        $mailer = SystemSetting::get('mail_mailer', 'log');
        
        config([
            'mail.default' => $mailer,
            'mail.mailers.smtp.host' => SystemSetting::get('mail_host', ''),
            'mail.mailers.smtp.port' => SystemSetting::get('mail_port', '587'),
            'mail.mailers.smtp.username' => SystemSetting::get('mail_username', ''),
            'mail.mailers.smtp.password' => SystemSetting::get('mail_password', ''),
            'mail.mailers.smtp.encryption' => SystemSetting::get('mail_encryption') === 'null' ? null : SystemSetting::get('mail_encryption', 'tls'),
            'mail.from.address' => SystemSetting::get('mail_from_address', 'noreply@example.com'),
            'mail.from.name' => SystemSetting::get('mail_from_name', config('app.name')),
        ]);
    }
}
